<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInputStokOpnameRequest;
use App\Models\ItemStokOpname;
use App\Models\PeriodeStokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InputStokOpnameController extends Controller
{
    public $pageTitle = "Input Laporan Stok Opname";

    public function __construct()
    {
        // Kosongkan constructor agar aman saat perintah Artisan (seperti route:list) dijalankan
    }

    public function create()
    {
        $pageTitle = $this->pageTitle;

        // Ambil periode yang aktif dengan aman (tidak error jika kosong)
        $activePeriodeRecord = PeriodeStokOpname::where('is_active', true)->first();
        $activePeriode = $activePeriodeRecord ? $activePeriodeRecord->id : null;

        $items = collect();

        if ($activePeriode) {
            $items = ItemStokOpname::with('varian.produk')
                ->where('periode_stok_opname_id', $activePeriode)
                ->get()
                ->map(function ($q) {
                    $namaProduk = $q->varian && $q->varian->produk ? $q->varian->produk->nama_produk : '';
                    $namaVarian = $q->varian ? $q->varian->nama_varian : '';

                    $produk = trim($namaProduk . ' ' . $namaVarian);
                    $q->setAttribute('produk', $produk);
                    return $q;
                });
        }

        return view('stok-opname.input.create', compact('pageTitle', 'items'));
    }

    public function update(UpdateInputStokOpnameRequest $request, $id)
    {
        $activePeriodeRecord = PeriodeStokOpname::where('is_active', true)->first();
        $activePeriode = $activePeriodeRecord ? $activePeriodeRecord->id : null;

        if (!$activePeriode) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada periode stok opname yang aktif.']);
        }

        $item = ItemStokOpname::find($id);

        if (!$item) {
            return redirect()->back()->withErrors(['error' => 'Item stok opname tidak ditemukan.']);
        }

        $isSelisih = (int) $request->jumlah_dilaporkan != (int) $item->jumlah_stok;

        $item->update([
            'jumlah_dilaporkan' => $request->jumlah_dilaporkan,
            'status' => $isSelisih ? 'selisih' : 'sesuai',
            'keterangan' => $request->keterangan,
            'petugas' => Auth::user()->name
        ]);

        $itemStokOpname = ItemStokOpname::where(
            'periode_stok_opname_id',
            $activePeriode
        )
            ->where('status', 'belum dilaporkan')
            ->count();

        if ($itemStokOpname == 0) {
            PeriodeStokOpname::where('id', $activePeriode)
                ->update(['is_completed' => true]);
        } else {
            PeriodeStokOpname::where('id', $activePeriode)
                ->update(['is_completed' => false]);
        }

        toast()->success('Item Stok Opname berhasil dilaporkan');
        return redirect()->route('stok-opname.input-data.create');
    }
}
