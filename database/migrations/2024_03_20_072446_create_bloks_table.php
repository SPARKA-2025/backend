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
        Schema::create('bloks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_fakultas');
            $table->string('nama');
            $table->string('panjang');
            $table->string('lebar');
            $table->string('panjang_area');
            $table->string('lebar_area');
            $table->string('ukuran_box');
            $table->longText('deskripsi');
            $table->foreign('id_fakultas')  
                    ->references('id')
                    ->on('fakultas')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloks');
    }
};
