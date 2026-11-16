<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PengajuanIzinController;
use Illuminate\Support\Facades\Route;

// Autentikasi Publik
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Terproteksi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Autentikasi & User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard & Profil Pegawai
    Route::get('/pegawai/profil', [PegawaiController::class, 'profil']);
    Route::get('/pegawai/dashboard', [PegawaiController::class, 'dashboard']);

    // Absensi & Pengaturan
    Route::get('/absensi/pengaturan', [AbsensiController::class, 'pengaturan']);
    Route::post('/absensi/masuk', [AbsensiController::class, 'masuk']);
    Route::post('/absensi/pulang', [AbsensiController::class, 'pulang']);
    Route::get('/absensi/hari-ini', [AbsensiController::class, 'hariIni']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'riwayat']);
    Route::get('/absensi/detail/{id}', [AbsensiController::class, 'detail']);

    // Pengajuan Izin
    Route::get('/pengajuan-izin', [PengajuanIzinController::class, 'index']);
    Route::post('/pengajuan-izin', [PengajuanIzinController::class, 'store']);
    Route::get('/pengajuan-izin/{id}', [PengajuanIzinController::class, 'show']);

    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::patch('/notifikasi/{id}/dibaca', [NotifikasiController::class, 'tandaiDibaca']);
});
