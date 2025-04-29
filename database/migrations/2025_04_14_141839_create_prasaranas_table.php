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
        Schema::create('prasaranas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->cascadeOnDelete();
            $table->json('ruang_belajar')->nullable();
            $table->json('ruang_bermain')->nullable();
            $table->json('ruang_pimpinan')->nullable();
            $table->json('ruang_sumber_belajar')->nullable();
            $table->json('ruang_guru')->nullable();
            $table->json('ruang_tata_usaha')->nullable();
            $table->json('kamar_mandi')->nullable();
            $table->json('kamar_kecil')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prasaranas');
    }
};
