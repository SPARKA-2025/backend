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
        Schema::create('cctv_data', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_kamera');
            $table->unsignedBigInteger('id_fakultas');
            $table->unsignedBigInteger('id_blok');
            $table->string('url');
            $table->string('x');
            $table->string('y');
            $table->string('angle');
            $table->foreign('id_fakultas')  
                    ->references('id')
                    ->on('fakultas')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->foreign('id_blok')  
                    ->references('id')
                    ->on('bloks')
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
        Schema::dropIfExists('cctv_data');
    }
};
