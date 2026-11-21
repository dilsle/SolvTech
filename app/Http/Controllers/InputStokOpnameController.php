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

    public $activePeriode;
    public $items;
    public function __construct()
    {
        $periode = PeriodeStokOpname::where('is_active', true)->first();
        $this->activePeriode = $periode?->id;

        $this->items = $periode
            ? ItemStokOpname::with('varian.produk')
                ->where('periode_stok_opname_id', $periode->id)
                ->get()
                ->map(function ($q) {
                    $q->setAttribute('produk', $q->varian->produk->nama_produk . ' ' . $q->varian->nama_varian);
                    return $q;
                })
            : collect();
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
        if (!$this->activePeriode) {
            return response()->json(['message' => 'Tidak ada periode stok opname yang aktif.'], 422);
        }

        $item = ItemStokOpname::where('periode_stok_opname_id', $this->activePeriode)->findOrFail($id);

        // Barang yang sudah dilaporkan dikunci untuk pegawai. Hanya admin yang boleh mengubahnya.
        if ($item->status !== 'belum dilaporkan' && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => "Barang ini sudah dilaporkan oleh {$item->petugas}. Hubungi admin jika perlu diubah.",
            ], 422);
        }

        $isSelisih = (int) $request->jumlah_dilaporkan !== (int) $item->jumlah_stok;

        $item->update([
            'jumlah_dilaporkan' => $request->jumlah_dilaporkan,
            'status' => $isSelisih ? 'selisih' : 'sesuai',
            'keterangan' => $request->keterangan,
            'petugas' => Auth::user()->name,
        ]);

        $belumDilaporkan = ItemStokOpname::where('periode_stok_opname_id', $this->activePeriode)
            ->where('status', 'belum dilaporkan')
            ->count();

        PeriodeStokOpname::where('id', $this->activePeriode)
            ->update(['is_completed' => $belumDilaporkan === 0]);

        return response()->json(['message' => 'Item stok opname berhasil dilaporkan.']);
    }
}
