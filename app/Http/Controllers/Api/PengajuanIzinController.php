<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;

class PengajuanIzinController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        $query = PengajuanIzin::where('pegawai_id', $pegawai->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->get()->map(fn($item) => $this->formatIzin($item));

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengajuan izin berhasil diambil.',
            'data' => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:Izin,Sakit,Cuti',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|min:5',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $pegawai = $request->user()->pegawai;
        if (!$pegawai || $pegawai->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun pegawai tidak aktif.',
                'data' => null,
            ], 403);
        }

        // Cek pengajuan tumpang tindih (overlap) yang statusnya Menunggu atau Disetujui
        $overlap = PengajuanIzin::where('pegawai_id', $pegawai->id)
            ->whereIn('status', ['Menunggu', 'Disetujui'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai])
                  ->orWhereBetween('tanggal_selesai', [$request->tanggal_mulai, $request->tanggal_selesai])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('tanggal_mulai', '<=', $request->tanggal_mulai)
                         ->where('tanggal_selesai', '>=', $request->tanggal_selesai);
                  });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki pengajuan izin pada rentang tanggal tersebut.',
                'data' => null,
            ], 422);
        }

        $pathBukti = null;
        if ($request->hasFile('bukti')) {
            $pathBukti = $request->file('bukti')->store('izin/bukti', 'public');
        }

        $izin = PengajuanIzin::create([
            'pegawai_id' => $pegawai->id,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'bukti' => $pathBukti,
            'status' => 'Menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil dikirim dan menunggu persetujuan Admin.',
            'data' => $this->formatIzin($izin),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $pegawai = $request->user()->pegawai;

        $izin = PengajuanIzin::with('admin')
            ->where('pegawai_id', $pegawai->id)
            ->where('id', $id)
            ->first();

        if (!$izin) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan izin tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pengajuan izin berhasil diambil.',
            'data' => $this->formatIzin($izin),
        ], 200);
    }

    private function formatIzin(PengajuanIzin $izin): array
    {
        return [
            'id' => $izin->id,
            'jenis' => $izin->jenis,
            'tanggal_mulai' => $izin->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $izin->tanggal_selesai?->format('Y-m-d'),
            'alasan' => $izin->alasan,
            'bukti_url' => $izin->bukti ? asset('storage/' . $izin->bukti) : null,
            'status' => $izin->status,
            'catatan_admin' => $izin->catatan_admin,
            'diproses_oleh' => $izin->admin?->name,
            'diproses_pada' => $izin->diproses_pada?->format('Y-m-d H:i:s'),
            'created_at' => $izin->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
