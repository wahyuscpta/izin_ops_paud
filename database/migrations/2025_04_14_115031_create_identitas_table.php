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
        Schema::create('identitas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->cascadeOnDelete();
            $table->string('nama_lembaga')->nullable();
            $table->text('alamat_identitas')->nullable();
            $table->char('kabupaten_identitas', 4)->nullable();
            $table->foreign('kabupaten_identitas')->references('id')->on('regencies')->onDelete('cascade');
            $table->char('kecamatan_identitas', 7)->nullable();
            $table->foreign('kecamatan_identitas')->references('id')->on('districts')->onDelete('cascade');
            $table->char('desa_identitas', 10)->nullable();
            $table->foreign('desa_identitas')->references('id')->on('villages')->onDelete('cascade');
            $table->string('no_telepon_identitas')->nullable();
            $table->date('tgl_didirikan')->nullable();
            $table->date('tgl_terdaftar')->nullable();
            $table->string('no_registrasi')->nullable();
            $table->string('no_surat_keputusan')->nullable();
            $table->string('rumpun_pendidikan')->nullable();
            $table->enum('jenis_pendidikan', ['tk', 'kb', 'tpa', 'sps', 'kursus'])->default('tk')->nullable();
            $table->enum('jenis_lembaga', ['induk', 'cabang'])->default('induk')->nullable();
            $table->boolean('has_cabang')->default(0)->nullable();
            $table->string('jumlah_cabang')->nullable()->nullable();
            $table->string('nama_lembaga_induk')->nullable();
            $table->text('alamat_lembaga_induk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identitas');
    }
};
