<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KartuStok;
use App\Models\TransaksiRetur;
use App\Models\TransaksiReturItem;
use App\Models\VarianProduk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiReturController extends Controller
{
    public $pageTitle = 'Transaksi Retur';

    public function index() {
        $pageTitle= $this->pageTitle;
        $search = request()->query('search');
        $perPage = request()->query('perpage') ?? 5;

        $query = TransaksiRetur::query();

        if ($search) {
            $query->where('nomor_retur', 'like','%' . $search . '%')
                ->orWhereHas('transaksi', function($q) use($search) {
                    $q->where('nomor_transaksi', 'like', '%' . $search . '%')
                    ->orWhere('pengirim', 'like', '%' . $search . '%');
                });
        }

        $retur= $query->orderBy('created_at', 'DESC')->paginate($perPage)->appends(request()->query());
        return view('transaksi-retur.index', compact('pageTitle','retur'));
    }
    public function create() {
        $pageTitle =$this->pageTitle;
        return view('transaksi-retur.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_transaksi' => 'required',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string',
        ], [
            'nomor_transaksi.required' => 'Pilih transaksi terlebih dahulu.',
            'items.required' => 'Tidak ada barang yang akan diretur.',
            'items.min' => 'Tidak ada barang yang akan diretur.',
            'items.*.qty.min' => 'Qty retur minimal 1.',
        ]);

        return DB::transaction(function () use ($request) {
            $transaksi = Transaksi::with('items')
                ->where('nomor_transaksi', $request->nomor_transaksi)
                ->where('jenis_transaksi', 'pengeluaran')
                ->lockForUpdate()
                ->first();

            if (!$transaksi) {
                throw ValidationException::withMessages(['items' => 'Transaksi tidak ditemukan.']);
            }

            // Aturan: satu transaksi hanya boleh diretur satu kali
            if (TransaksiRetur::where('nomor_transaksi', $transaksi->nomor_transaksi)->exists()) {
                throw ValidationException::withMessages([
                    'items' => 'Transaksi ini sudah pernah diretur dan tidak bisa diretur lagi.',
                ]);
            }

            // Gabungkan baris yang sama (jaga-jaga kalau terkirim dobel)
            $daftar = collect($request->items)->groupBy('id')->map(function ($rows) {
                return [
                    'id'   => (int) $rows->first()['id'],
                    'qty'  => (int) $rows->sum('qty'),
                    'note' => $rows->pluck('note')->filter()->implode('; '),
                ];
            })->values();

            $baris = [];
            $totalQty = 0;
            $totalHarga = 0;

            foreach ($daftar as $d) {
                $itemAsal = $transaksi->items->firstWhere('id', $d['id']);

                if (!$itemAsal) {
                    throw ValidationException::withMessages(['items' => 'Ada barang yang tidak termasuk dalam transaksi ini.']);
                }

                if ($d['qty'] > $itemAsal->qty) {
                    throw ValidationException::withMessages([
                        'items' => "Qty retur {$itemAsal->produk} melebihi jumlah yang dibeli ({$itemAsal->qty} pcs).",
                    ]);
                }

                $varian = VarianProduk::where('nomor_sku', $itemAsal->nomor_sku)->lockForUpdate()->first();

                if (!$varian || $varian->stok_varian < $d['qty']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$itemAsal->produk} tidak cukup untuk barang pengganti.",
                    ]);
                }

                $subTotal = $itemAsal->harga * $d['qty'];
                $totalQty += $d['qty'];
                $totalHarga += $subTotal;

                $baris[] = compact('itemAsal', 'varian', 'subTotal') + ['qty' => $d['qty'], 'note' => $d['note']];
            }

            $nomorRetur = TransaksiRetur::generateNomorRetur();

            $retur = TransaksiRetur::create([
                'nomor_retur'     => $nomorRetur,
                'nomor_transaksi' => $transaksi->nomor_transaksi,
                'jumlah_barang'   => $totalQty,
                'jumlah_harga'    => $totalHarga,
                'petugas'         => Auth::user()->name,
            ]);

            foreach ($baris as $b) {
                TransaksiReturItem::create([
                    'transaksi_retur_id' => $retur->id,
                    'produk'             => $b['itemAsal']->produk,
                    'varian'             => $b['varian']->nama_varian,
                    'nomor_batch'        => $b['itemAsal']->nomor_batch,
                    'nomor_sku'          => $b['itemAsal']->nomor_sku,
                    'qty'                => $b['qty'],
                    'harga'              => $b['itemAsal']->harga,
                    'sub_total'          => $b['subTotal'],
                    'note'               => $b['note'],
                ]);

                // Barang pengganti diambil dari stok, barang cacat tidak kembali ke stok
                $b['varian']->decrement('stok_varian', $b['qty']);

                KartuStok::create([
                    'nomor_transaksi' => $nomorRetur,
                    'jenis_transaksi' => 'retur',
                    'nomor_sku'       => $b['itemAsal']->nomor_sku,
                    'jumlah_keluar'   => $b['qty'],
                    'stok_akhir'      => $b['varian']->fresh()->stok_varian,
                    'petugas'         => Auth::user()->name,
                ]);
            }

            toast()->success('Transaksi retur berhasil disimpan');

            return response()->json([
                'success'      => true,
                'redirect_url' => route('transaksi-retur.index'),
            ]);
        });
    }

    public function show($nomor_retur) {
        $pageTitle = "Detail " . $this->pageTitle;
        $transaksi = TransaksiRetur::with('transaksi', 'items')->where('nomor_retur', $nomor_retur)->first();
        $transaksi->tanggal_retur = Carbon::parse($transaksi->created_at)->locale('id')->translatedFormat('l, d F Y');
        $transaksi->jumlah_barang = $transaksi->items->sum('qty');

        return view('transaksi-retur.show', compact('transaksi', 'pageTitle'));
    }
}
