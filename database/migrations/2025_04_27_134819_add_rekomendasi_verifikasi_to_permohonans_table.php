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
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('no_surat_rekomendasi')->nullable()->after('no_permohonan'); // sesuaikan existing_field
            $table->date('tgl_surat_rekomendasi')->nullable()->after('no_surat_rekomendasi');
            $table->string('pemberi_rekomendasi')->nullable()->after('tgl_surat_rekomendasi');
            $table->string('no_verifikasi')->nullable()->after('pemberi_rekomendasi');
            $table->date('tgl_verifikasi')->nullable()->after('no_verifikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_surat_rekomendasi',
                'tanggal_surat_rekomendasi',
                'pemberi_rekomendasi',
                'nomor_verifikasi',
                'tanggal_verifikasi',
            ]);
        });
    }
};
