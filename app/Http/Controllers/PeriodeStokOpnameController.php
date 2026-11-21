<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\PeriodeStokOpname;
use App\Models\VarianProduk;
use App\Models\ItemStokOpname;
use App\Http\Requests\StorePeriodeStokOpnameRequest;
use App\Http\Requests\UpdatePeriodeStokOpnameRequest;
use Illuminate\Support\Facades\DB;

class PeriodeStokOpnameController extends Controller
{
    public $pageTitle = "Periode Stok Opname";

    public function index()
    {
        confirmDelete('Menghapus data periode akan menghapus semua data stok opname yang sudah ada, lanjutkan untuk menghapus data ini ?');
        $pageTitle = $this->pageTitle;
        $dataPeriode = PeriodeStokOpname::orderBy('created_at', 'DESC')->get()->map(function ($q) {
            $tanggalMulai = Carbon::parse($q->tanggal_mulai)->locale('id')->translatedFormat('d M Y');
            $tanggalSelesai = Carbon::parse($q->tanggal_selesai)->locale('id')->translatedFormat('d M Y');
            $periode = $tanggalMulai . ' s/d ' . $tanggalSelesai;
            return [
                'id'                    => $q->id,
                'periode'               => $periode,
                'is_active'             => $q->is_active,
                'is_completed'          => $q->is_completed,
                'jumlah_barang'         => $q->jumlah_barang,
                'jumlah_barang_sesuai'  => ItemStokOpname::jumlahDilaporkan($q->id, 'sesuai'),
                'jumlah_barang_selisih' => ItemStokOpname::jumlahDilaporkan($q->id, 'selisih')
            ];
        });

        return view('stok-opname.periode.index', compact('pageTitle', 'dataPeriode'));
    }

   public function store(StorePeriodeStokOpnameRequest $request)
    {
        $isActive = $request->boolean('is_active');
        $varian = VarianProduk::all();

        DB::transaction(function () use ($request, $isActive, $varian) {
            $newPeriode = PeriodeStokOpname::create([
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'is_active' => $isActive,
                'jumlah_barang' => count($varian),
            ]);

            foreach ($varian as $item) {
                ItemStokOpname::create([
                    'periode_stok_opname_id' => $newPeriode->id,
                    'nomor_sku' => $item->nomor_sku,
                    'jumlah_stok' => $item->stok_varian,
                ]);
            }

            if ($isActive) {
                PeriodeStokOpname::where('is_active', true)
                    ->where('id', '!=', $newPeriode->id)
                    ->update(['is_active' => false]);
            }
        });

        return response()->json(['message' => 'Periode Stok Opname berhasil ditambahkan']);
    }

    public function update(UpdatePeriodeStokOpnameRequest $request, $id)
    {
        $isActive = $request->boolean('is_active');

        $periode = PeriodeStokOpname::findOrFail($id);
        $periode->update([
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $isActive,
        ]);

        if ($isActive) {
            PeriodeStokOpname::where('is_active', true)
                ->where('id', '!=', $id)
                ->update(['is_active' => false]);
        }

        return response()->json(['message' => 'Periode Stok Opname berhasil diubah']);
    }
    public function destroy($id)
    {
        $periode = PeriodeStokOpname::findOrFail($id);

        if ($periode->is_active) {
            toast()->error('Periode Stok Opname sedang aktif, tidak dapat dihapus');
            return redirect()->route('stok-opname.periode.index');
        }

        $periode->delete();

        toast()->success('Periode Stok Opname berhasil dihapus');
        return redirect()->route('stok-opname.periode.index');
    }

    public function show($periode) {
        $pageTitle = $this->pageTitle;

        // Memuat relasi bertingkat secara aman menggunakan eager loading
        $dataPeriode = PeriodeStokOpname::with('items.varian.produk')->findOrFail($periode);

        $tanggalMulai = Carbon::parse($dataPeriode->tanggal_mulai)->locale('id')->translatedFormat('d M Y');
        $tanggalSelesai = Carbon::parse($dataPeriode->tanggal_selesai)->locale('id')->translatedFormat('d M Y');
        $formattedPeriode = $tanggalMulai . ' s/d ' . $tanggalSelesai;

        $dataPeriode['periode'] = $formattedPeriode;
        $dataPeriode['jumlah_barang_sesuai'] = ItemStokOpname::jumlahDilaporkan($dataPeriode->id, 'sesuai');
        $dataPeriode['jumlah_barang_selisih'] = ItemStokOpname::jumlahDilaporkan($dataPeriode->id, 'selisih');

        // Menggunakan null-safe operator (?->) untuk mencegah error pada data null
        $dataPeriode['items'] = $dataPeriode->items->map(function ($q) {
            $namaProduk = $q->varian?->produk?->nama_produk ?? 'Produk Tidak Ditemukan';
            $namaVarian = $q->varian?->nama_varian ?? '';

            $q->setAttribute('produk', trim($namaProduk . ' ' . $namaVarian));
            return $q;
        });

        return view('stok-opname.periode.show', compact('pageTitle', 'dataPeriode'));
    }
}
