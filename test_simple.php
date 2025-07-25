<?php

echo "Testing database connection and YouTube URL save...\n";

try {
    // Koneksi ke database MySQL Docker
    $pdo = new PDO('mysql:host=mysql;dbname=sparka_db', 'sparka_user', 'sparka123');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected successfully!\n";
    
    // Test URL YouTube
    $youtube_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    
    // Insert data CCTV dengan URL YouTube
    $stmt = $pdo->prepare('INSERT INTO cctv_data (url, angle, id_part, x, y, jenis_kamera, offset_x, offset_y, id_fakultas, id_blok, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
    $result = $stmt->execute([$youtube_url, '0', 1, '0', '0', 'IP Camera', '0', '0', 1, 1]);
    
    if ($result) {
        $id = $pdo->lastInsertId();
        echo "✅ YouTube URL berhasil disimpan dengan ID: $id\n";
        echo "URL yang disimpan: $youtube_url\n";
        
        // Hapus data test
        $deleteStmt = $pdo->prepare('DELETE FROM cctv_data WHERE id = ?');
        $deleteStmt->execute([$id]);
        echo "✅ Data test berhasil dihapus\n";
        echo "\n🎉 KESIMPULAN: URL YouTube dapat disimpan ke database tanpa masalah!\n";
    } else {
        echo "❌ Gagal menyimpan URL YouTube\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}