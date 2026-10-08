<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeVarianProdukRequest;
use App\Http\Requests\updatevarianProdukRequest;
use App\Http\Requests\updateVarianProdukRequest as RequestsUpdateVarianProdukRequest;
use App\Models\KartuStok;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VarianProdukController extends Controller
{
    public function store(storeVarianProdukRequest $request)
    {
        $fileName = time() . '.' . $request->file('gambar_varian')->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('varian-produk', $request->file('gambar_varian'), $fileName);
        VarianProduk::create([
            'produk_id' => $request->produk_id,
            'nomor_sku' =>  VarianProduk::generateNomorSku(),
            'nama_varian' => $request->nama_varian,
            'harga_varian' => $request->harga_varian,
            'stok_varian' => $request->stok_varian,
            'gambar_varian' => $fileName
        ]);

        return response()->json([
            'message' => 'Data berhasil disimpan'
        ]);
    }

    public function update(UpdateVarianProdukRequest $request, $varian_produk)
    {
        $isAdjusment = false;
        $varian = VarianProduk::findOrFail($varian_produk);


        if($request->stok_varian != $varian->stok_varian) {
            $isAdjusment = true;
        }
        $fileName = $varian->gambar_varian;

        if($request->file('gambar_varian')) {
            Storage::disk('public')->delete('varian-produk/'.$varian->gambar_varian);
            $fileName = time() . '.' . $request->file('gambar_varian')->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('varian-produk',$request->file('gambar_varian'), $fileName);
        }

        $varian->update([
            'nama_varian' => $request->nama_varian,
            'harga_varian' => $request->harga_varian,
            'stok_varian' => $request->stok_varian,
            'gambar_varian' => $fileName
        ]);

        if($isAdjusment) {
            KartuStok::create([
                'jenis_transaksi' => 'adjustment',
                'nomor_sku'       => $varian->nomor_sku,
                'stok_akhir'      => $varian->stok_varian,
                'petugas'         => Auth::user()->name
            ]);
        }

        return response()->json([
            'message' => 'Data berhasil diupdate'
        ]);
    }


    public function destroy($varian_produk)
    {
        $varian = VarianProduk::findOrFail($varian_produk);
        Storage::disk('public')->delete('varian-produk/' . $varian->gambar_varian);
        $varian->delete();
        toast()->success('Data berhasil dihapus');
        return redirect()->route('master-data.produk.show', $varian->produk_id);
    }
}
