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
        Schema::create('permohonans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('no_permohonan')->unique();
            $table->date('tgl_permohonan');
            $table->date('tgl_status_terakhir');
            $table->enum('status_permohonan', [
                'draft',
                'menunggu_verifikasi',
                'menunggu_validasi_lapangan',
                'proses_penerbitan_izin',
                'izin_diterbitkan',
                'permohonan_ditolak'])
            ->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonans');
    }
};
