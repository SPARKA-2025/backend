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
        Schema::table('cctv_data', function (Blueprint $table) {
            $table->string('offset_x')->default('0');
            $table->string('offset_y')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctv_data', function (Blueprint $table) {
            $table->dropColumn('offset_x');
            $table->dropColumn('offset_y');
        });
    }
};
