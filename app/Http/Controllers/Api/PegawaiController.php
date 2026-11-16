<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\PengajuanIzin;
use App\Models\PengaturanAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function profil(Request $request)
    {
        $user = $request->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'Data pegawai tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil pegawai berhasil diambil.',
            'data' => [
                'id' => $pegawai->id,
                'user_id' => $pegawai->user_id,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'email' => $user->email,
                'jenis_kelamin' => $pegawai->jenis_kelamin,
                'no_hp' => $pegawai->no_hp,
                'alamat' => $pegawai->alamat,
                'jabatan' => $pegawai->jabatan,
                'tanggal_masuk' => $pegawai->tanggal_masuk?->format('Y-m-d'),
                'status' => $pegawai->status,
            ],
        ], 200);
    }

    public function dashboard(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        if (!$pegawai || $pegawai->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun pegawai tidak aktif atau tidak ditemukan.',
                'data' => null,
            ], 403);
        }

        $now = Carbon::now('Asia/Jakarta');
        $todayStr = $now->toDateString();

        // Pengaturan operasional bengkel
        $pengaturan = PengaturanAbsensi::first();
        $bukaMinggu = $pengaturan ? (bool) $pengaturan->buka_minggu : false;
        $isLiburKhusus = HariLibur::whereDate('tanggal', $todayStr)->exists();
        $isHariKerja = !($now->isSunday() && !$bukaMinggu) && !$isLiburKhusus;

        // Absensi hari ini
        $absensiHariIni = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $todayStr)
            ->first();

        // Rekap bulan berjalan
        $absensiBulanIni = Absensi::where('pegawai_id', $pegawai->id)
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->get();

        $izinMenunggu = PengajuanIzin::where('pegawai_id', $pegawai->id)
            ->where('status', 'Menunggu')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'tanggal_hari_ini' => $todayStr,
                'hari_ini_label' => $now->locale('id')->translatedFormat('l, d F Y'),
                'is_hari_kerja' => $isHariKerja,
                'pegawai' => [
                    'id' => $pegawai->id,
                    'nip' => $pegawai->nip,
                    'nama' => $pegawai->nama,
                    'jabatan' => $pegawai->jabatan,
                ],
                'jam_operasional' => [
                    'jam_masuk' => $pengaturan?->jam_masuk ?? '08:00:00',
                    'batas_toleransi' => $pengaturan?->batas_toleransi ?? '08:30:00',
                    'jam_pulang' => $pengaturan?->jam_pulang ?? '17:00:00',
                ],
                'absensi_hari_ini' => $absensiHariIni ? [
                    'id' => $absensiHariIni->id,
                    'jam_masuk' => $absensiHariIni->jam_masuk,
                    'jam_keluar' => $absensiHariIni->jam_keluar,
                    'status' => $absensiHariIni->status,
                ] : null,
                'ringkasan_bulan_ini' => [
                    'hadir' => $absensiBulanIni->where('status', 'Hadir')->count(),
                    'terlambat' => $absensiBulanIni->where('status', 'Terlambat')->count(),
                    'izin' => $absensiBulanIni->where('status', 'Izin')->count(),
                    'sakit' => $absensiBulanIni->where('status', 'Sakit')->count(),
                    'izin_menunggu' => $izinMenunggu,
                ],
            ],
        ], 200);
    }
}
