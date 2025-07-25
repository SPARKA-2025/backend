<?php

echo "=== Testing YouTube URL Streaming via HTTP API ===\n";

// Test YouTube URL
$youtubeUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
$backendUrl = 'http://localhost:8000';
$streamingServerUrl = 'http://localhost:8010';

echo "🔧 Backend running locally at: {$backendUrl}\n";
echo "🎥 Streaming server at: {$streamingServerUrl}\n";

echo "📺 Testing YouTube URL: {$youtubeUrl}\n";

// First, let's test if streaming server is accessible
echo "\n🔍 Testing streaming server health...\n";
$healthCheck = @file_get_contents($streamingServerUrl . '/health');
if ($healthCheck) {
    echo "✅ Streaming server is accessible\n";
    echo "Health response: {$healthCheck}\n";
} else {
    echo "❌ Streaming server is not accessible at {$streamingServerUrl}\n";
}

// Test direct streaming server API
echo "\n🚀 Testing direct streaming server API...\n";

$postData = json_encode([
    'sourceUrl' => $youtubeUrl,
    'cctvId' => 'test_youtube_123'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ],
        'content' => $postData,
        'timeout' => 30
    ]
]);

$response = @file_get_contents($streamingServerUrl . '/api/stream/start', false, $context);

if ($response) {
    echo "✅ Streaming server response: {$response}\n";
    
    $responseData = json_decode($response, true);
    if ($responseData && isset($responseData['success']) && $responseData['success']) {
        echo "🎉 Streaming started successfully!\n";
        
        // Wait a bit
        echo "⏳ Waiting 3 seconds...\n";
        sleep(3);
        
        // Check status
        echo "\n📊 Checking stream status...\n";
        $statusResponse = @file_get_contents($streamingServerUrl . '/api/stream/status/test_youtube_123');
        if ($statusResponse) {
            echo "Status response: {$statusResponse}\n";
        }
        
        // Stop stream
        echo "\n🛑 Stopping stream...\n";
        $stopData = json_encode(['cctvId' => 'test_youtube_123']);
        $stopContext = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($stopData)
                ],
                'content' => $stopData,
                'timeout' => 10
            ]
        ]);
        
        $stopResponse = @file_get_contents($streamingServerUrl . '/api/stream/stop', false, $stopContext);
        if ($stopResponse) {
            echo "Stop response: {$stopResponse}\n";
        }
    } else {
        echo "❌ Streaming failed to start\n";
    }
} else {
    echo "❌ Failed to connect to streaming server API\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: {$error['message']}\n";
    }
}

echo "\n=== Test completed ===\n";