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
        Schema::create('log_kendaraans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_fakultas');
            $table->unsignedBigInteger('id_blok');
            $table->dateTime('capture_time');
            $table->string('vehicle');
            $table->string('plat_nomor');
            // $table->string('location');
            $table->longText('image');
            // $table->dateTime('tanggal_masuk');
            // $table->dateTime('tanggal_keluar');
            $table->timestamps();
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_kendaraans');
    }
};
