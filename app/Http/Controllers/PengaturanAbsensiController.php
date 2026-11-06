<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use App\Models\PengaturanAbsensi;
use Illuminate\Http\Request;

class PengaturanAbsensiController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanAbsensi::firstOrCreate([], [
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '17:00:00',
            'batas_toleransi' => '08:30:00',
            'latitude' => -6.8924000,
            'longitude' => 107.5424000,
            'radius_meter' => 100,
            'buka_minggu' => false,
        ]);

        $hariLiburs = HariLibur::orderBy('tanggal', 'desc')->paginate(10);

        return view('pengaturan-absensi.index', compact('pengaturan', 'hariLiburs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'batas_toleransi' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:10',
            'buka_minggu' => 'required|boolean',
        ]);

        $pengaturan = PengaturanAbsensi::first();
        $pengaturan->update($request->all());

        return redirect()->route('pengaturan-absensi.index')->with('success', 'Pengaturan absensi berhasil diperbarui.');
    }

    public function storeLibur(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_liburs,tanggal',
            'keterangan' => 'required|string|max:255',
        ]);

        HariLibur::create($request->all());

        return redirect()->route('pengaturan-absensi.index')->with('success', 'Hari libur khusus berhasil ditambahkan.');
    }

    public function destroyLibur($id)
    {
        HariLibur::findOrFail($id)->delete();
        return redirect()->route('pengaturan-absensi.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}
