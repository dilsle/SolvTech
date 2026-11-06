<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajuanIzinController extends Controller
{
    public function index(Request $request)
    {
        $jumlahMenunggu = PengajuanIzin::where('status', 'Menunggu')->count();
        $jumlahDisetujui = PengajuanIzin::where('status', 'Disetujui')->count();
        $jumlahDitolak = PengajuanIzin::where('status', 'Ditolak')->count();

        $query = PengajuanIzin::with('pegawai')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_mulai', '<=', $request->tanggal)
                  ->whereDate('tanggal_selesai', '>=', $request->tanggal);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pegawai', function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        $pengajuans = $query->paginate(10)->withQueryString();

        return view('pengajuan-izin.index', compact('pengajuans', 'jumlahMenunggu', 'jumlahDisetujui', 'jumlahDitolak'));
    }

    public function show($id)
    {
        $pengajuan = PengajuanIzin::with(['pegawai', 'admin'])->findOrFail($id);
        return view('pengajuan-izin.show', compact('pengajuan'));
    }

    public function approve(Request $request, $id)
    {
        $pengajuan = PengajuanIzin::findOrFail($id);

        if ($pengajuan->status !== 'Menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $pengajuan->update([
                'status' => 'Disetujui',
                'catatan_admin' => $request->input('catatan_admin'),
                'disetujui_oleh' => Auth::id(),
                'diproses_pada' => Carbon::now(),
            ]);

            // Integrasi ke Rekap Absensi: Buat catatan absensi sejumlah rentang tanggal pengajuan
            $currentDate = Carbon::parse($pengajuan->tanggal_mulai);
            $endDate = Carbon::parse($pengajuan->tanggal_selesai);

            $statusAbsensi = match ($pengajuan->jenis) {
                'Izin' => 'Izin',
                'Sakit' => 'Sakit',
                'Cuti' => 'Izin', // Cuti dicatat sebagai Izin/Keterangan resmi
                default => 'Izin',
            };

            while ($currentDate <= $endDate) {
                // Cek apakah sudah ada catatan absensi pada tanggal tersebut
                $existingAbsensi = Absensi::where('pegawai_id', $pengajuan->pegawai_id)
                    ->whereDate('tanggal', $currentDate)
                    ->first();

                if ($existingAbsensi) {
                    $existingAbsensi->update([
                        'status' => $statusAbsensi,
                        'keterangan' => 'Pengajuan ' . $pengajuan->jenis . ' Disetujui: ' . $pengajuan->alasan,
                    ]);
                } else {
                    Absensi::create([
                        'pegawai_id' => $pengajuan->pegawai_id,
                        'tanggal' => $currentDate->copy(),
                        'status' => $statusAbsensi,
                        'keterangan' => 'Pengajuan ' . $pengajuan->jenis . ' Disetujui',
                    ]);
                }

                $currentDate->addDay();
            }

            DB::commit();
            return redirect()->route('pengajuan-izin.index')->with('success', 'Pengajuan izin berhasil disetujui dan diintegrasikan ke absensi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'required|string|min:5',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi jika menolak pengajuan.',
        ]);

        $pengajuan = PengajuanIzin::findOrFail($id);

        if ($pengajuan->status !== 'Menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $pengajuan->update([
            'status' => 'Ditolak',
            'catatan_admin' => $request->catatan_admin,
            'disetujui_oleh' => Auth::id(),
            'diproses_pada' => Carbon::now(),
        ]);

        return redirect()->route('pengajuan-izin.index')->with('success', 'Pengajuan izin berhasil ditolak.');
    }
}
