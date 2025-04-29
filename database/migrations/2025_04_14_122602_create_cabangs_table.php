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
        Schema::create('cabangs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('identitas_id');
            $table->foreign('identitas_id')->references('id')->on('identitas')->cascadeOnDelete();
            $table->string('nama_lembaga_cabang')->nullable();
            $table->text('alamat_lembaga_cabang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabangs');
    }
};
