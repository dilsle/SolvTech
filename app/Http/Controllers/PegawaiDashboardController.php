<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use App\Models\Transaksi;
use App\Models\VarianProduk;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PegawaiDashboardController extends Controller
{
    public const BATAS_STOK_MENIPIS = 10;

    public function index(Request $request)
    {
        $user = $request->user();
        $pegawai = $user->pegawai;

        $hariIni = Carbon::today();
        $awalBulan = Carbon::now()->startOfMonth();

        // Penjualan milik pegawai ini saja (dicocokkan lewat kolom petugas)
        $milik = fn () => Transaksi::where('jenis_transaksi', 'pengeluaran')
            ->where('petugas', $user->name);

        $ringkasan = fn ($query) => $query
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total_harga), 0) as total')
            ->first();

        $penjualanHariIni  = $ringkasan($milik()->whereDate('created_at', $hariIni));
        $penjualanBulanIni = $ringkasan($milik()->where('created_at', '>=', $awalBulan));

        // Grafik 7 hari terakhir
        $awalGrafik = Carbon::today()->subDays(6);
        $perHari = $milik()
            ->where('created_at', '>=', $awalGrafik)
            ->selectRaw('DATE(created_at) as tgl, SUM(total_harga) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $grafik = ['labels' => [], 'total' => []];
        for ($d = $awalGrafik->copy(); $d->lte($hariIni); $d->addDay()) {
            $grafik['labels'][] = $d->copy()->locale('id')->translatedFormat('D, d');
            $grafik['total'][]  = (int) ($perHari[$d->toDateString()] ?? 0);
        }

        $transaksiTerakhir = $milik()->latest()->limit(5)->get();

        // Stok menipis
        $stokMenipisTotal = VarianProduk::where('stok_varian', '<', self::BATAS_STOK_MENIPIS)->count();
        $stokMenipis = VarianProduk::with('produk')
            ->where('stok_varian', '<', self::BATAS_STOK_MENIPIS)
            ->orderBy('stok_varian')
            ->limit(5)
            ->get();

        // Pengajuan izin terakhir
        $izinTerakhir = PengajuanIzin::where('pegawai_id', $pegawai->id)->latest()->first();

        return view('pegawai-area.dashboard', compact(
            'pegawai', 'penjualanHariIni', 'penjualanBulanIni', 'grafik',
            'transaksiTerakhir', 'stokMenipis', 'stokMenipisTotal', 'izinTerakhir'
        ));
    }
}
