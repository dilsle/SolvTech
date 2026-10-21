<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_returs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_retur_id')->nullable()->contrained('transaksi_return')->cascadeOnUpdate();
            $table->string('produk');
            $table->string('nomor_sku');
            $table->string('nomor_batch');
            $table->string('varian');
            $table->string('qty');
            $table->string('harga');
            $table->string('sub_total');
            $table->string('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_returs');
    }
};
