<?php

namespace App\Http\Controllers;

use App\Models\KartuStok;
use App\Http\Resources\KartuStokResource;
use Carbon\Carbon;
use App\Exports\ExportLaporanKartuStok;
use App\Http\Requests\ExportKartuStokRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\VarianProduk;

class KartuStokController extends Controller
{
    public function kartuStok($nomor_sku)
    {
        $query = KartuStok::where('nomor_sku', $nomor_sku)->orderBy('created_at', 'DESC')->paginate(5);
        return KartuStokResource::collection($query);
    }

    public function exportLaporan(ExportKartuStokRequest $request)
{
    $tanggalAwal = Carbon::parse($request->tanggal_awal)->startOfDay();
    $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();

    $nomorSku = $request->nomor_sku;
    $jenisTransaksi = $request->jenis_transaksi;

    $varian = VarianProduk::where('nomor_sku', $nomorSku)->first();

    $query = KartuStok::where('nomor_sku', $nomorSku)
        ->where('jenis_transaksi', $jenisTransaksi)
        ->whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
        ->orderBy('created_at', 'DESC')
        ->get();

    return Excel::download(
        new ExportLaporanKartuStok($query, $varian, $tanggalAwal, $tanggalAkhir),
        'laporan-kartu-stok.xlsx'
    );
}
}
