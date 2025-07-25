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
        Schema::table('parkirs', function (Blueprint $table) {
            // Drop tanggal_masuk and tanggal_keluar columns if they exist
            if (Schema::hasColumn('parkirs', 'tanggal_masuk')) {
                $table->dropColumn('tanggal_masuk');
            }
            if (Schema::hasColumn('parkirs', 'tanggal_keluar')) {
                $table->dropColumn('tanggal_keluar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parkirs', function (Blueprint $table) {
            // Re-add the columns if migration is rolled back
            $table->dateTime('tanggal_masuk')->nullable();
            $table->dateTime('tanggal_keluar')->nullable();
        });
    }
};
