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
        Schema::table('cctv_data', function (Blueprint $table) {
            $table->string('hls_url')->nullable()->after('url')->comment('HLS streaming URL for converted RTSP streams');
            $table->boolean('stream_active')->default(false)->after('hls_url')->comment('Whether the stream is currently active');
            $table->timestamp('last_stream_start')->nullable()->after('stream_active')->comment('Last time stream was started');
            $table->timestamp('last_stream_stop')->nullable()->after('last_stream_start')->comment('Last time stream was stopped');
            
            // Add index for better performance
            $table->index(['stream_active']);
            $table->index(['last_stream_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctv_data', function (Blueprint $table) {
            $table->dropIndex(['stream_active']);
            $table->dropIndex(['last_stream_start']);
            $table->dropColumn(['hls_url', 'stream_active', 'last_stream_start', 'last_stream_stop']);
        });
    }
};