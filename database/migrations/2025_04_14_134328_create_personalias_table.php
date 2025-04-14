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
        Schema::create('personalias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->onDelete('cascade');
            $table->unsignedInteger('guru_wni_lk')->default(0);
            $table->unsignedInteger('guru_wni_pr')->default(0);
            $table->unsignedInteger('guru_wni_jumlah')->default(0);
            $table->unsignedInteger('asisten_wni_lk')->default(0);
            $table->unsignedInteger('asisten_wni_pr')->default(0);
            $table->unsignedInteger('asisten_wni_jumlah')->default(0);
            $table->unsignedInteger('tata_usaha_wni_lk')->default(0);
            $table->unsignedInteger('tata_usaha_wni_pr')->default(0);
            $table->unsignedInteger('tata_usaha_wni_jumlah')->default(0);
            $table->unsignedInteger('pesuruh_wni_lk')->default(0);
            $table->unsignedInteger('pesuruh_wni_pr')->default(0);
            $table->unsignedInteger('pesuruh_wni_jumlah')->default(0);
            $table->unsignedInteger('guru_wna_lk')->default(0);
            $table->unsignedInteger('guru_wna_pr')->default(0);
            $table->unsignedInteger('guru_wna_jumlah')->default(0);
            $table->unsignedInteger('asisten_wna_lk')->default(0);
            $table->unsignedInteger('asisten_wna_pr')->default(0);
            $table->unsignedInteger('asisten_wna_jumlah')->default(0);
            $table->unsignedInteger('tata_usaha_wna_lk')->default(0);
            $table->unsignedInteger('tata_usaha_wna_pr')->default(0);
            $table->unsignedInteger('tata_usaha_wna_jumlah')->default(0);
            $table->unsignedInteger('pesuruh_wna_lk')->default(0);
            $table->unsignedInteger('pesuruh_wna_pr')->default(0);
            $table->unsignedInteger('pesuruh_wna_jumlah')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personalias');
    }
};
