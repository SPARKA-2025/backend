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
        Schema::table('slot__parkirs', function (Blueprint $table) {
            // Tambahkan foreign key constraint untuk id_part saja dengan nama custom
            // (id_blok foreign key sudah ada di migrasi create table)
            $table->foreign('id_part', 'fk_slot_parkirs_id_part')
                  ->references('id')
                  ->on('parts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slot__parkirs', function (Blueprint $table) {
            // Drop foreign key constraint untuk id_part saja dengan nama custom
            $table->dropForeign('fk_slot_parkirs_id_part');
        });
    }
};