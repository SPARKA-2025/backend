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
        Schema::create('token_operator', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_operator');
            $table->string('api_token', 512);
            $table->timestamp('expired_at')->nullable();
            $table->foreign('id_operator')
                    ->references('id')
                    ->on('operators')
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
        Schema::dropIfExists('token_operator');
    }
};
