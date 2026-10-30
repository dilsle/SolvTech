<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Periode filter (default: bulan ini)
        $start = $request->input('start', now()->startOfMonth()->toDateString());
        $end   = $request->input('end',   now()->endOfMonth()->toDateString());

        // Periode pembanding (rentang hari yang sama persis sebelum periode ini)
        $hari      = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $prevStart = Carbon::parse($start)->subDays($hari)->toDateString();
        $prevEnd   = Carbon::parse($start)->subDay()->toDateString();

        $kini  = $this->ringkas($start, $end);
        $lalu  = $this->ringkas($prevStart, $prevEnd);

        // Persentase naik/turun dibanding periode sebelumnya (null = tidak ada pembanding)
        $persen = fn ($now, $prev) => $prev > 0 ? round(($now - $prev) / $prev * 100, 1) : null;
        $tren = [
            'transaksi' => $persen($kini['total'],  $lalu['total']),
            'masuk'     => $persen($kini['masuk'],  $lalu['masuk']),
            'keluar'    => $persen($kini['keluar'], $lalu['keluar']),
        ];

        // ====== Grafik: barang masuk vs keluar, 6 bulan terakhir sampai bulan akhir periode ======
        $awal  = Carbon::parse($end)->startOfMonth()->subMonths(5);
        $akhir = Carbon::parse($end)->endOfMonth();

        $rows = DB::table('transaksis')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, jenis_transaksi, SUM(jumlah_barang) as total")
            ->whereBetween('created_at', [$awal, $akhir])
            ->groupBy('ym', 'jenis_transaksi')
            ->get();

        $peta = [];
        foreach ($rows as $r) {
            $peta[$r->ym][$r->jenis_transaksi] = (int) $r->total;
        }

        $grafik = ['bulan' => [], 'masuk' => [], 'keluar' => []];
        for ($i = 0; $i < 6; $i++) {
            $b = $awal->copy()->addMonths($i);
            $grafik['bulan'][]  = $b->locale('id')->translatedFormat('M');
            $grafik['masuk'][]  = $peta[$b->format('Y-m')]['pemasukan']   ?? 0;
            $grafik['keluar'][] = $peta[$b->format('Y-m')]['pengeluaran'] ?? 0;
        }

        // ====== Stok perlu perhatian (< 10) ======
        $stokMenipis = DB::table('varian_produks')->where('stok_varian', '<', 10)->count();

        $stokMinimal = DB::table('varian_produks')
            ->join('produks', 'produks.id', '=', 'varian_produks.produk_id')
            ->selectRaw("CONCAT(produks.nama_produk, ' ', varian_produks.nama_varian) as nama,
                         varian_produks.nomor_sku as sku,
                         varian_produks.stok_varian as stok")
            ->where('varian_produks.stok_varian', '<', 10)
            ->orderBy('varian_produks.stok_varian')
            ->limit(5)
            ->get();

        // ====== Kenaikan harga beli yang belum dikonfirmasi ======
        $kenaikanHarga = DB::table('laporan_kenaikan_hargas')
            ->leftJoin('varian_produks', 'varian_produks.nomor_sku', '=', 'laporan_kenaikan_hargas.nomor_sku')
            ->leftJoin('produks', 'produks.id', '=', 'varian_produks.produk_id')
            ->where('laporan_kenaikan_hargas.is_confirm', false)
            ->selectRaw("laporan_kenaikan_hargas.*,
                         CONCAT(IFNULL(produks.nama_produk, laporan_kenaikan_hargas.nomor_sku),
                                IFNULL(CONCAT(' ', varian_produks.nama_varian), '')) as nama")
            ->latest('laporan_kenaikan_hargas.created_at')
            ->limit(5)
            ->get();

        return view('home', [
            'start' => $start, 'end' => $end,
            'totalTransaksi' => $kini['total'],
            'barangMasuk'    => $kini['masuk'],
            'barangKeluar'   => $kini['keluar'],
            'biayaKeluar'    => $kini['biaya_keluar'],     // beli stok
            'biayaDiterima'  => $kini['biaya_diterima'],   // hasil jual
            'tren' => $tren,
            'grafik' => $grafik,
            'stokMenipis' => $stokMenipis,
            'stokMinimal' => $stokMinimal,
            'kenaikanHarga' => $kenaikanHarga,
        ]);
    }

    /** Rekap transaksi pada rentang tanggal tertentu. */
    private function ringkas(string $from, string $to): array
    {
        $row = DB::table('transaksis')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw("COUNT(*) as total,
                COALESCE(SUM(CASE WHEN jenis_transaksi = 'pemasukan'   THEN jumlah_barang END), 0) as masuk,
                COALESCE(SUM(CASE WHEN jenis_transaksi = 'pengeluaran' THEN jumlah_barang END), 0) as keluar,
                COALESCE(SUM(CASE WHEN jenis_transaksi = 'pemasukan'   THEN total_harga END), 0)   as biaya_keluar,
                COALESCE(SUM(CASE WHEN jenis_transaksi = 'pengeluaran' THEN total_harga END), 0)   as biaya_diterima")
            ->first();

        return array_map('intval', (array) $row);
    }
}
