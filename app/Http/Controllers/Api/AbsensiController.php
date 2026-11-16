<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\PengaturanAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function pengaturan()
    {
        $pengaturan = PengaturanAbsensi::first();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan absensi berhasil diambil.',
            'data' => [
                'jam_masuk' => $pengaturan?->jam_masuk ?? '08:00:00',
                'jam_pulang' => $pengaturan?->jam_pulang ?? '17:00:00',
                'batas_toleransi' => $pengaturan?->batas_toleransi ?? '08:30:00',
                'latitude' => (float) ($pengaturan?->latitude ?? -6.8924000),
                'longitude' => (float) ($pengaturan?->longitude ?? 107.5424000),
                'radius_meter' => (int) ($pengaturan?->radius_meter ?? 100),
                'buka_minggu' => (bool) ($pengaturan?->buka_minggu ?? false),
            ],
        ], 200);
    }

    public function hariIni(Request $request)
    {
        $pegawai = $request->user()->pegawai;
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $absensi = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $today)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi hari ini berhasil diambil.',
            'data' => $absensi ? $this->formatAbsensi($absensi) : null,
        ], 200);
    }

    public function masuk(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:3072',
        ]);

        $pegawai = $request->user()->pegawai;
        if (!$pegawai || $pegawai->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun pegawai tidak aktif.',
                'data' => null,
            ], 403);
        }

        $now = Carbon::now('Asia/Jakarta');
        $todayStr = $now->toDateString();

        // 1. Cek Hari Operasional & Hari Libur
        $pengaturan = PengaturanAbsensi::first();
        $bukaMinggu = $pengaturan ? (bool) $pengaturan->buka_minggu : false;

        if ($now->isSunday() && !$bukaMinggu) {
            return response()->json([
                'success' => false,
                'message' => 'Bengkel tutup pada hari Minggu. Absensi tidak dapat dilakukan.',
                'data' => null,
            ], 422);
        }

        $libur = HariLibur::whereDate('tanggal', $todayStr)->first();
        if ($libur) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini libur (' . $libur->keterangan . '). Absensi tidak diperlukan.',
                'data' => null,
            ], 422);
        }

        // 2. Cek apakah sudah absen masuk / tercatat izin hari ini
        $existing = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $todayStr)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki catatan kehadiran hari ini (' . $existing->status . ').',
                'data' => $this->formatAbsensi($existing),
            ], 422);
        }

        // 3. Tentukan Status Keterlambatan (Toleransi 08:30 WIB)
        $batasToleransi = Carbon::parse($todayStr . ' ' . ($pengaturan?->batas_toleransi ?? '08:30:00'), 'Asia/Jakarta');
        $statusKehadiran = $now->greaterThan($batasToleransi) ? 'Terlambat' : 'Hadir';

        // 4. Simpan Foto Selfie ke Storage
        $pathFoto = $request->file('foto')->store('absensi/masuk', 'public');

        $absensi = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal' => $todayStr,
            'jam_masuk' => $now->format('H:i:s'),
            'latitude' => $request->latitude ?? 0,
            'longitude' => $request->longitude ?? 0,
            'foto_masuk' => $pathFoto,
            'status' => $statusKehadiran,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi masuk berhasil dicatat (' . $statusKehadiran . ').',
            'data' => $this->formatAbsensi($absensi),
        ], 201);
    }

    public function pulang(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:3072',
        ]);

        $pegawai = $request->user()->pegawai;
        if (!$pegawai || $pegawai->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun pegawai tidak aktif.',
                'data' => null,
            ], 403);
        }

        $now = Carbon::now('Asia/Jakarta');
        $todayStr = $now->toDateString();

        $absensi = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $todayStr)
            ->first();

        if (!$absensi || !$absensi->jam_masuk) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absensi masuk hari ini.',
                'data' => null,
            ], 422);
        }

        if ($absensi->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi pulang hari ini.',
                'data' => $this->formatAbsensi($absensi),
            ], 422);
        }

        // Simpan Foto Pulang ke Storage
        $pathFoto = $request->file('foto')->store('absensi/keluar', 'public');

        $absensi->update([
            'jam_keluar' => $now->format('H:i:s'),
            'foto_keluar' => $pathFoto,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi pulang berhasil dicatat.',
            'data' => $this->formatAbsensi($absensi->fresh()),
        ], 200);
    }

    public function riwayat(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        $query = Absensi::where('pegawai_id', $pegawai->id)
            ->orderBy('tanggal', 'desc');

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $riwayat = $query->limit(60)->get()->map(fn($item) => $this->formatAbsensi($item));

        return response()->json([
            'success' => true,
            'message' => 'Riwayat absensi berhasil diambil.',
            'data' => $riwayat,
        ], 200);
    }

    public function detail(Request $request, $id)
    {
        $pegawai = $request->user()->pegawai;

        $absensi = Absensi::where('pegawai_id', $pegawai->id)
            ->where('id', $id)
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Data absensi tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail absensi berhasil diambil.',
            'data' => $this->formatAbsensi($absensi),
        ], 200);
    }

    private function formatAbsensi(Absensi $absensi): array
    {
        return [
            'id' => $absensi->id,
            'tanggal' => $absensi->tanggal?->format('Y-m-d'),
            'tanggal_formatted' => $absensi->tanggal?->locale('id')->translatedFormat('l, d F Y'),
            'jam_masuk' => $absensi->jam_masuk,
            'jam_keluar' => $absensi->jam_keluar,
            'latitude' => $absensi->latitude,
            'longitude' => $absensi->longitude,
            'foto_masuk_url' => $absensi->foto_masuk ? asset('storage/' . $absensi->foto_masuk) : null,
            'foto_keluar_url' => $absensi->foto_keluar ? asset('storage/' . $absensi->foto_keluar) : null,
            'status' => $absensi->status,
            'keterangan' => $absensi->keterangan,
        ];
    }
}
