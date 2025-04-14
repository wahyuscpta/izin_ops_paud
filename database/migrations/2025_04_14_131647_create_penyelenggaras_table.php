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
        Schema::create('penyelenggaras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->cascadeOnDelete();
            $table->string('nama_perorangan')->nullable();
            $table->string('agama_perorangan')->nullable();
            $table->string('kewarganegaraan_perorangan')->nullable();
            $table->string('ktp_perorangan')->nullable();
            $table->string('tanggal_perorangan')->nullable();
            $table->text('alamat_perorangan')->nullable();
            $table->string('telepon_perorangan')->nullable();
            $table->char('kabupaten_perorangan', 4)->nullable();
            $table->foreign('kabupaten_perorangan')->references('id')->on('regencies')->onDelete('cascade');
            $table->string('nama_badan')->nullable();
            $table->string('agama_badan')->nullable();
            $table->string('akte_badan')->nullable();
            $table->string('nomor_badan')->nullable();
            $table->string('tanggal_badan')->nullable();
            $table->text('alamat_badan')->nullable();
            $table->string('telepon_badan')->nullable();
            $table->char('kabupaten_badan', 4)->nullable();
            $table->foreign('kabupaten_badan')->references('id')->on('regencies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyelenggaras');
    }
};
