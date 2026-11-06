<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();

            // Relasi dengan akun users
            $table->foreignId('user_id')
                  ->nullable()
                  ->unique()
                  ->constrained('users')
                  ->nullOnDelete();

            // Identitas pegawai
            $table->string('nip')->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('no_hp', 20);
            $table->text('alamat')->nullable();

            // Informasi pekerjaan
            $table->string('jabatan');
            $table->date('tanggal_masuk');
            $table->enum('status', ['Aktif', 'Nonaktif'])
                  ->default('Aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
