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
        Schema::create('peserta_didiks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->cascadeOnDelete();
            $table->enum('jalur_penerimaan_tes', ['ya', 'tidak'])->nullable();
            $table->enum('tata_usaha_penerimaan', ['ada', 'tidak'])->nullable();
            $table->string('jumlah_tiap_angkatan')->nullable();
            $table->string('jumlah_menyelesaikan')->nullable();
            $table->string('jumlah_sekarang_lk')->nullable();
            $table->string('jumlah_sekarang_pr')->nullable();
            $table->string('jumlah_sekarang_total')->nullable();
            $table->string('jumlah_tamat_lk')->nullable();
            $table->string('jumlah_tamat_pr')->nullable();
            $table->string('jumlah_tamat_total')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_didiks');
    }
};
