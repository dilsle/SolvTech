<?php

use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\VarianProdukController;
use App\Http\Controllers\KartuStokController;
use App\Models\KategoriProduk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::middleware('auth')->group(function(){
    Route::prefix('master-data')->name('master-data.')->group(function() {
        Route::resource('kategori-produk', KategoriProdukController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('varian-produk', VarianProdukController::class)->only(['store', 'update', 'destroy']);
        Route::resource('stok-barang', StokBarangController::class)->only('index');
    });

    Route::get('/kartu-stok/{nomor_sku}', [KartuStokController::class, 'kartuStok'])->name('kartu-stok');
});
