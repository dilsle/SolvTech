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
        Schema::table('item_stok_opnames', function (Blueprint $table) {
            $table->enum('status', ['sesuai', 'selisih', 'belum dilaporkan'])->default('belum dilaporkan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_stok_opnames', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
