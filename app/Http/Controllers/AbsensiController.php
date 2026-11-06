<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AbsensiLogKoreksi;
use App\Models\Pegawai;
use App\Models\PengaturanAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();

        $totalPegawaiAktif = Pegawai::where('status', 'Aktif')->count();
        $hadirHariIni = Absensi::where('tanggal', $today)->whereIn('status', ['Hadir', 'Terlambat'])->count();
        $terlambatHariIni = Absensi::where('tanggal', $today)->where('status', 'Terlambat')->count();
        $izinHariIni = Absensi::where('tanggal', $today)->where('status', 'Izin')->count();
        $sakitHariIni = Absensi::where('tanggal', $today)->where('status', 'Sakit')->count();
        $sudahAbsenIds = Absensi::where('tanggal', $today)->pluck('pegawai_id');
        $belumAbsen = $totalPegawaiAktif - Pegawai::where('status', 'Aktif')->whereIn('id', $sudahAbsenIds)->count();

        $query = Absensi::with('pegawai')->latest('tanggal');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                  ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pegawai', function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensis = $query->paginate(10)->withQueryString();

        return view('absensi.index', compact(
            'totalPegawaiAktif', 'hadirHariIni', 'terlambatHariIni',
            'izinHariIni', 'sakitHariIni', 'belumAbsen', 'absensis'
        ));
    }

    public function show($id)
    {
        $absensi = Absensi::with(['pegawai.user', 'logKoreksis.admin'])->findOrFail($id);
        return view('absensi.show', compact('absensi'));
    }

    public function koreksi(Request $request, $id)
    {
        $request->validate([
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'status' => 'required|in:Hadir,Terlambat,Izin,Sakit',
            'alasan' => 'required|string|min:5',
        ]);

        $absensi = Absensi::findOrFail($id);

        $dataSebelumnya = [
            'jam_masuk' => $absensi->jam_masuk,
            'jam_keluar' => $absensi->jam_keluar,
            'status' => $absensi->status,
            'keterangan' => $absensi->keterangan,
        ];

        DB::beginTransaction();
        try {
            $absensi->update([
                'jam_masuk' => $request->jam_masuk,
                'jam_keluar' => $request->jam_keluar,
                'status' => $request->status,
                'keterangan' => ($absensi->keterangan ? $absensi->keterangan . ' | ' : '') . 'Dikoreksi oleh Admin: ' . $request->alasan,
            ]);

            $dataSesudah = [
                'jam_masuk' => $absensi->jam_masuk,
                'jam_keluar' => $absensi->jam_keluar,
                'status' => $absensi->status,
                'keterangan' => $absensi->keterangan,
            ];

            AbsensiLogKoreksi::create([
                'absensi_id' => $absensi->id,
                'admin_id' => Auth::id(),
                'data_sebelumnya' => $dataSebelumnya,
                'data_sesudah' => $dataSesudah,
                'alasan' => $request->alasan,
            ]);

            DB::commit();
            return redirect()->route('absensi.show', $absensi->id)->with('success', 'Absensi berhasil dikoreksi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function rekap(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $pegawaiId = $request->input('pegawai_id');

        $pegawais = Pegawai::where('status', 'Aktif')->when($pegawaiId, function($q) use ($pegawaiId) {
            $q->where('id', $pegawaiId);
        })->get();

        $pengaturan = PengaturanAbsensi::first();
        $bukaMinggu = $pengaturan ? $pengaturan->buka_minggu : false;

        $startOfMonth = Carbon::create($tahun, $bulan, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $now = Carbon::now();
        if ($endOfMonth->isFuture()) {
            $endOfMonth = $now->isSameMonth($startOfMonth) ? $now->copy() : $startOfMonth->copy();
        }

        $totalHariKerja = 0;
        $curr = $startOfMonth->copy();
        while ($curr <= $endOfMonth) {
            if (!($curr->isSunday() && !$bukaMinggu)) {
                $totalHariKerja++;
            }
            $curr->addDay();
        }

        $rekapData = [];
        foreach ($pegawais as $p) {
            $absensis = Absensi::where('pegawai_id', $p->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            $hadir = $absensis->where('status', 'Hadir')->count();
            $terlambat = $absensis->where('status', 'Terlambat')->count();
            $izin = $absensis->where('status', 'Izin')->count();
            $sakit = $absensis->where('status', 'Sakit')->count();

            $totalMasuk = $hadir + $terlambat + $izin + $sakit;
            $tanpaKeterangan = max(0, $totalHariKerja - $totalMasuk);

            $rekapData[] = [
                'pegawai' => $p,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'tanpa_keterangan' => $tanpaKeterangan,
                'total_hari_kerja' => $totalHariKerja,
            ];
        }

        $allPegawai = Pegawai::where('status', 'Aktif')->get();

        return view('absensi.rekap', compact('rekapData', 'bulan', 'tahun', 'pegawaiId', 'allPegawai', 'totalHariKerja'));
    }
}
