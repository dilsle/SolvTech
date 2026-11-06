<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_izins', function (Blueprint $table) {
            $table->id();

            // Relasi dengan pegawai
            $table->foreignId('pegawai_id')
                  ->constrained('pegawais')
                  ->cascadeOnDelete();

            // Informasi pengajuan
            $table->enum('jenis', [
                'Izin',
                'Sakit',
                'Cuti'
            ]);

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->text('alasan');

            // Bukti surat atau dokumen pendukung
            $table->string('bukti')->nullable();

            // Status persetujuan admin
            $table->enum('status', [
                'Menunggu',
                'Disetujui',
                'Ditolak'
            ])->default('Menunggu');

            $table->text('catatan_admin')->nullable();

            // Admin yang memproses pengajuan
            $table->foreignId('disetujui_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('diproses_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izins');
    }
};
