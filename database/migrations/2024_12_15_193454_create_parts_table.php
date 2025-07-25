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
        Schema::create('parts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_blok');
            $table->string('nama')->default('Horizontal');
            $table->integer('column')->default(1); // test bug 
            $table->integer('row')->default(1);    // test bug 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
