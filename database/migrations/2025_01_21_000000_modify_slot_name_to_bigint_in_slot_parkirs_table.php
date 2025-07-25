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
            // Change slot_name from integer to bigInteger to support large timestamp values
            $table->bigInteger('slot_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slot__parkirs', function (Blueprint $table) {
            // Revert back to integer
            $table->integer('slot_name')->change();
        });
    }
};