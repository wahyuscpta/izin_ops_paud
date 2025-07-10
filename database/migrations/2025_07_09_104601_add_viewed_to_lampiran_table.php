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
        Schema::table('lampirans', function (Blueprint $table) {
            $table->boolean('viewed')->default(false)->after('lampiran_path');
            $table->uuid('viewedBy')->nullable()->after('viewed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lampirans', function (Blueprint $table) {
            $table->dropColumn('viewed');
            $table->dropColumn('viewedBy');
        });
    }
};
