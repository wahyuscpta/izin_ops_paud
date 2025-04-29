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
        Schema::create('pengelolas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->cascadeOnDelete();
            $table->string('nama_pengelola')->nullable();
            $table->string('agama_pengelola')->nullable();
            $table->enum('jenis_kelamin_pengelola', ['l', 'p'])->nullable();
            $table->string('kewarganegaraan_pengelola')->nullable();
            $table->string('ktp_pengelola')->nullable();
            $table->string('tanggal_pengelola')->nullable();
            $table->text('alamat_pengelola')->nullable();
            $table->string('telepon_pengelola')->nullable();
            $table->char('kabupaten_pengelola', 4)->nullable();
            $table->foreign('kabupaten_pengelola')->references('id')->on('regencies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengelolas');
    }
};
