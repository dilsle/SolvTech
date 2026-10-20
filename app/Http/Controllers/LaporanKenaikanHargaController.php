<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LaporanKenaikanHarga;
use Illuminate\Http\Request;

class LaporanKenaikanHargaController extends Controller
{
    public function index()
    {
        $pageTitle = "Laporan Kenaikan Harga";
        $laporan= LaporanKenaikanHarga::with('varian','varian.produk')->where('is_confirm', false)->get();
        return view('laporan-kenaikan-harga.index', compact('pageTitle', 'laporan'));
    }
}
