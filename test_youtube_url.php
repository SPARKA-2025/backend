<?php

// Test penyimpanan URL YouTube menggunakan Laravel framework
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\cctvData;

try {
    echo "Testing YouTube URL save to database...\n";
    
    // Test URL YouTube
    $youtube_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    
    // Buat data CCTV dengan URL YouTube
    $cctvData = cctvData::create([
        'url' => $youtube_url,
        'angle' => '0',
        'id_part' => 1,
        'x' => '0',
        'y' => '0',
        'jenis_kamera' => 'IP Camera', // Tambahkan jenis_kamera yang required
        'offset_x' => '0',
        'offset_y' => '0'
    ]);
    
    if ($cctvData) {
        echo "✅ URL YouTube berhasil disimpan dengan ID: {$cctvData->id}\n";
        echo "URL yang disimpan: {$cctvData->url}\n";
        
        // Hapus data test
        $cctvData->delete();
        echo "✅ Data test berhasil dihapus\n";
        echo "\n🎉 Kesimpulan: URL YouTube dapat disimpan ke database tanpa masalah!\n";
    } else {
        echo "❌ Gagal menyimpan URL YouTube\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}