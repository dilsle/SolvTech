<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_absensis', function (Blueprint $table) {
            $table->id();
            $table->time('jam_masuk')->default('08:00:00');
            $table->time('jam_pulang')->default('17:00:00');
            $table->time('batas_toleransi')->default('08:30:00');
            $table->decimal('latitude', 10, 7)->default(-6.8924000); // Koordinat default Cimahi
            $table->decimal('longitude', 10, 7)->default(107.5424000);
            $table->integer('radius_meter')->default(100);
            $table->boolean('buka_minggu')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_absensis');
    }
};
