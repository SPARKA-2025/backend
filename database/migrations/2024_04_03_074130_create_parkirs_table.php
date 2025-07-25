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
        Schema::create('parkirs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_slot');
            $table->unsignedBigInteger('id_user');
            $table->string('plat_nomor');
            // $table->string('nama_pemesan');
            $table->string('jenis_mobil');
            $table->dateTime('waktu_booking');
            $table->dateTime('waktu_booking_berakhir');
            $table->timestamps();
            $table->foreign('id_slot')  
                    ->references('id')
                    ->on('slot__parkirs')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->foreign('id_user')  
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parkirs');
    }
};