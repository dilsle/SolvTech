<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\storeTransaksiMasukRequest;
use App\Models\Transaksi;
use App\Models\VarianProduk;
use App\Models\KartuStok;
use Illuminate\Support\Facades\Auth;

class TransaksiMasukController extends Controller
{
    public $pageTitle = 'Transaksi Masuk';
    public $jenisTransaksi = 'pemasukan';

    public function index() {
        $pageTitle = $this->pageTitle;
        return view('transaksi-masuk.index', compact('pageTitle'));
    }

    public function create()
    {
        $pageTitle = $this->pageTitle;
        return view('transaksi-masuk.create', compact('pageTitle'));
    }

    public function store(storeTransaksiMasukRequest $request)
    {
        $nomorTransaksi = Transaksi::generateNomorTransaksi($this->jenisTransaksi);
        $items = $request->items;

        $transaksi = Transaksi::create([
            'nomor_transaksi'   => $nomorTransaksi,
            'jenis_transaksi'   => $this->jenisTransaksi,
            'jumlah_barang'     => count($items),
            'total_harga'       => array_sum(array_column($items, 'subTotal')),
            'keterangan'        => $request->keterangan,
            'petugas'           => Auth::user()->name,
            'pengirim'          => $request->pengirim,
            'kontak'            => $request->kontak
        ]);

        foreach ($items as $item) {
            $parsedText = explode('-', $item['text']);
            $namaProduk = trim($parsedText[0] ?? '');
            $namaVarian = trim($parsedText[1] ?? ''); // Ambil varian jika ada di text select2

            $varian = VarianProduk::where('nomor_sku', $item['nomor_sku'])->first();

            $transaksi->items()->create([
                'transaksi_id'  => $transaksi->id,
                'produk'        => $namaProduk,
                'varian'        => $namaVarian, // Ditambahkan agar tidak error database
                'nomor_batch'   => $item['nomor_batch'],
                'qty'           => $item['qty'],
                'harga'         => $item['harga'],
                'sub_total'     => $item['subTotal'],
                'nomor_sku'     => $item['nomor_sku']
            ]);

            if ($varian) {
                $varian->increment('stok_varian', $item['qty']);

                KartuStok::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'jenis_transaksi' => 'in',
                    'nomor_sku'       => $item['nomor_sku'],
                    'jumlah_masuk'    => $item['qty'],
                    'stok_akhir'      => $varian->fresh()->stok_varian, // Ambil data terbaru
                    'petugas'         => Auth::user()->name
                ]);
            }
        }

        return response()->json([
            'success'      => true,
            'redirect_url' => route('transaksi-masuk.create')
        ]);
    }
}
