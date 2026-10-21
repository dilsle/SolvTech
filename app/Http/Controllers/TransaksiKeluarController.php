<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\VarianProduk;
use App\Models\KartuStok;
use App\Http\Requests\storeTransaksiKeluarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransaksiKeluarController extends Controller
{
    public $pageTitle = 'Transaksi Keluar';
    public $jenisTransaksi = 'pengeluaran';

    public function index() {
        $penerima = request()->query('penerima');
        $tanggalAwal = request()->query('tanggal_awal');
        $tanggalAkhir = request()->query('tanggal_akhir');
        $perPage = request()->query('perPage', 5);

        $query = Transaksi::query();
        $query->orderBy('created_at', 'DESC');
        $query->where('jenis_transaksi', $this->jenisTransaksi);

        if($penerima) {
            $query->where('penerima', 'like', '%'.$penerima.'%');
        }

        if ($tanggalAwal && $tanggalAkhir) {
            $tanggalAwal = Carbon::parse($tanggalAwal)->startOfDay();
            $tanggalAkhir = Carbon::parse($tanggalAkhir)->endOfDay();
            $query->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir]);
        }

        $transaksi = $query->paginate($perPage)->appends(request()->query());
        $pageTitle = $this->pageTitle;
        return view('transaksi-keluar.index', compact('pageTitle', 'transaksi'));
    }

    public function create()
    {
        $pageTitle = $this->pageTitle;
        return view('transaksi-keluar.create', compact('pageTitle'));
    }

    public function show($nomor_transaksi)
    {
        $pageTitle = "Detail " . $this->pageTitle;
        $transaksi = Transaksi::with('items')->where('nomor_transaksi', $nomor_transaksi)->firstOrFail();
        $transaksi->formated_date = Carbon::parse($transaksi->created_at)->locale('id')->translatedFormat('l, d F Y');
        return view('transaksi-keluar.show', compact('pageTitle', 'transaksi'));
    }

    public function store(storeTransaksiKeluarRequest $request)
    {
        $nomorTransaksi = Transaksi::generateNomorTransaksi($this->jenisTransaksi);
        $items = $request->items;

        $transaksi = Transaksi::create([
            'nomor_transaksi'   => $nomorTransaksi,
            'jenis_transaksi'   => $this->jenisTransaksi,
            'jumlah_barang'     => count($items),
            'total_harga'       => 0,
            'keterangan'        => $request->keterangan,
            'petugas'           => Auth::user()->name,
            'penerima'          => $request->penerima,
            'kontak'            => $request->kontak
        ]);

        foreach ($items as $item) {
            $parsedText = explode('-', $item['text']);
            $namaProduk = trim($parsedText[0] ?? '');
            $namaVarian = trim($parsedText[1] ?? ''); // Ambil varian jika ada di text select2
            $varian = VarianProduk::where('nomor_sku', $item['nomor_sku'])->first();

            if ($varian) {
                $transaksi->items()->create([
                    'transaksi_id'  => $transaksi->id,
                    'produk'        => $namaProduk,
                    'varian'        => $namaVarian, // Ditambahkan agar tidak error database
                    'qty'           => $item['qty'],
                    'harga'         => $varian->harga_varian,
                    'sub_total'     => $varian->harga_varian * $item['qty'],
                    'nomor_sku'     => $item['nomor_sku']
                ]);

                $varian->decrement('stok_varian', $item['qty']);

                KartuStok::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'jenis_transaksi' => 'out',
                    'nomor_sku'       => $item['nomor_sku'],
                    'jumlah_keluar'   => $item['qty'],
                    'stok_akhir'      => $varian->fresh()->stok_varian, // Ambil data terbaru
                    'petugas'         => Auth::user()->name
                ]);

                $transaksi->total_harga += $varian->harga_varian * $item['qty'];
                $transaksi->save();
            }
        }

        return response()->json([
            'success'      => true,
            'redirect_url' => route('transaksi-keluar.create')
        ]);
    }

    public function getTransaksiKeluar() {
        $search = request()->query('search');

        $transaksi = Transaksi::where('jenis_transaksi', $this->jenisTransaksi)
            ->where('nomor_transaksi', 'like', '%' . $search . '%')
            ->get()->map(function ($q){
                return [
                    'id'   => $q->id,
                    'text' => $q->nomor_transaksi
                ];
            });

        return response()->json($transaksi);
    }
}
