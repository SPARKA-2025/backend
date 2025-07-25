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
        Schema::create('slot__parkirs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_blok');
            $table->integer('slot_name');
            $table->string('status')->default('Kosong');
            $table->string('x');
            $table->string('y');
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
        Schema::dropIfExists('slot__parkirs');
    }
};
