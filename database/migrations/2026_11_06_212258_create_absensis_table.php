<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();

            // Relasi dengan pegawai
            $table->foreignId('pegawai_id')
                  ->constrained('pegawais')
                  ->cascadeOnDelete();

            // Informasi absensi
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();

            // Lokasi absensi (untuk APK nantinya)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Bukti foto absensi (opsional)
            $table->string('foto_masuk')->nullable();
            $table->string('foto_keluar')->nullable();

            // Status kehadiran
            $table->enum('status', [
                'Hadir',
                'Terlambat',
                'Izin',
                'Sakit'
            ])->default('Hadir');

            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Satu pegawai hanya satu catatan per tanggal
            $table->unique(['pegawai_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
