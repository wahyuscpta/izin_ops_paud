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
            $table->unsignedInteger('jumlah_tiap_angkatan')->default(0);
            $table->unsignedInteger('jumlah_menyelesaikan')->default(0);
            $table->unsignedInteger('jumlah_sekarang_lk')->default(0);
            $table->unsignedInteger('jumlah_sekarang_pr')->default(0);
            $table->unsignedInteger('jumlah_sekarang_total')->default(0);
            $table->unsignedInteger('jumlah_tamat_lk')->default(0);
            $table->unsignedInteger('jumlah_tamat_pr')->default(0);
            $table->unsignedInteger('jumlah_tamat_total')->default(0);
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
