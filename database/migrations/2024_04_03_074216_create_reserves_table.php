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
        Schema::create('reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_parkir');
            $table->unsignedBigInteger('id_user');
            $table->dateTime('tanggal_masuk');
            $table->dateTime('tanggal_keluar');
            $table->foreign('id_parkir')->references('id')->on('parkirs')->onDelete('cascade')->onUpdate('cascade');;
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserves');
    }


};
