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
        Schema::create('saranas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->cascadeOnDelete();
            $table->enum('buku_pelajaran', ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'])->nullable();
            $table->enum('alat_permainan_edukatif', ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'])->nullable();
            $table->enum('meja_kursi', ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'])->nullable();
            $table->enum('papan_tulis', ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'])->nullable();
            $table->enum('alat_tata_usaha', ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'])->nullable();
            $table->enum('listrik', ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'])->nullable();
            $table->enum('air_bersih', ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saranas');
    }
};
