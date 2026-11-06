<?php

use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\VarianProdukController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\TransaksiMasukController;
use App\Http\Controllers\TransaksiKeluarController;
use App\Http\Controllers\ExportLaporanTransaksiController;
use App\Http\Controllers\LaporanKenaikanHargaController;
use App\Http\Controllers\TransaksiReturController;
use App\Http\Controllers\PeriodeStokOpnameController;
use App\Http\Controllers\InputStokOpnameController;
use App\Http\Controllers\ItemStokOpnameController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PengajuanIzinController;
use App\Http\Controllers\PengaturanAbsensiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function(){

    Route::prefix('get-data')->name('get-data.')->group(function () {
        Route::get('/varian-produk', [VarianProdukController::class, 'getAllVarianJson'])->name('varian-produk');
        Route::get('/transaksi-keluar', [TransaksiKeluarController::class, 'getTransaksiKeluar'])->name('transaksi-keluar');
        Route::get('/transaksi-keluar/{nomor_transaksi}', [TransaksiKeluarController::class, 'getTransaksiKeluarItems'])->name('transaksi-keluar-items');
    });

    Route::post('export-laporan-transaksi', [ExportLaporanTransaksiController::class, 'exportLaporanTransaksi'])->name('export-laporan-transaksi');
    Route::post('export-laporan-kartu-stok', [KartuStokController::class, 'exportLaporan']);

    Route::resource('laporan-kenaikan-harga', LaporanKenaikanHargaController::class)->only('index', 'update');

    Route::prefix('master-data')->name('master-data.')->group(function() {
        Route::resource('kategori-produk', KategoriProdukController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('varian-produk', VarianProdukController::class)->only(['store', 'update', 'destroy']);
        Route::resource('stok-barang', StokBarangController::class)->only('index');
    });

    // ==========================================
    // MODUL PEGAWAI, ABSENSI & IZIN
    // ==========================================

    // Manajemen Pegawai
    Route::resource('pegawai', PegawaiController::class);

    // Absensi
    Route::get('absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
    Route::post('absensi/koreksi/{id}', [AbsensiController::class, 'koreksi'])->name('absensi.koreksi');
    Route::resource('absensi', AbsensiController::class)->only(['index', 'show']);

    // Pengajuan Izin
    Route::patch('pengajuan-izin/{id}/approve', [PengajuanIzinController::class, 'approve'])->name('pengajuan-izin.approve');
    Route::patch('pengajuan-izin/{id}/reject', [PengajuanIzinController::class, 'reject'])->name('pengajuan-izin.reject');
    Route::resource('pengajuan-izin', PengajuanIzinController::class)->only(['index', 'show']);

    // Pengaturan Absensi
    Route::get('pengaturan-absensi', [PengaturanAbsensiController::class, 'index'])->name('pengaturan-absensi.index');
    Route::put('pengaturan-absensi', [PengaturanAbsensiController::class, 'update'])->name('pengaturan-absensi.update');

    // ==========================================
    // ROUTE LAMA
    // ==========================================

    Route::get('/kartu-stok/{nomor_sku}', [KartuStokController::class, 'kartuStok'])->name('kartu-stok');
    Route::resource('transaksi-masuk', TransaksiMasukController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('transaksi-keluar', TransaksiKeluarController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('transaksi-retur', TransaksiReturController::class)->only(['index', 'create', 'store', 'show']);

    Route::prefix('stok-opname')->name('stok-opname.')->group(function () {
        Route::resource('periode', PeriodeStokOpnameController::class);
        Route::resource('input-data', InputStokOpnameController::class);
        Route::post('/update-produk', [ItemStokOpnameController::class, 'updateProduk'])->name('update-produk');
    });

});
