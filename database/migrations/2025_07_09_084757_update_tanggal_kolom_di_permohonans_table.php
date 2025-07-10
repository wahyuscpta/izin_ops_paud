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
            $table->dropColumn(['tgl_status_terakhir']);

            $table->date('tgl_verifikasi_berkas')->nullable()->after('tgl_verifikasi');
            $table->date('tgl_validasi_lapangan')->nullable()->after('tgl_verifikasi_berkas');
            $table->date('tgl_proses_penerbitan_izin')->nullable()->after('tgl_validasi_lapangan');
            $table->date('tgl_izin_terbit')->nullable()->after('tgl_proses_penerbitan_izin');
            $table->date('tgl_tolak')->nullable()->after('tgl_izin_terbit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->date('tgl_status_terakhir')->nullable();

            $table->dropColumn([
                'tgl_verifikasi_berkas',
                'tgl_validasi_lapangan',
                'tgl_proses_penerbitan_izin',
                'tgl_izin_terbit',
                'tgl_tolak'
            ]);
        });
    }
};
