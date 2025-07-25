<?php

// Simple test endpoint for YouTube streaming without authentication
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['youtube_url']) || !isset($input['cctv_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'youtube_url and cctv_id are required'
        ]);
        exit;
    }
    
    $youtubeUrl = $input['youtube_url'];
    $cctvId = $input['cctv_id'];
    
    // Test streaming server directly
    $streamingApiUrl = 'http://localhost:8010';
    
    $postData = json_encode([
        'sourceUrl' => $youtubeUrl,
        'cctvId' => $cctvId
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $postData,
            'timeout' => 30
        ]
    ]);
    
    $response = file_get_contents($streamingApiUrl . '/api/stream/start', false, $context);
    
    if ($response === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to connect to streaming server'
        ]);
        exit;
    }
    
    $streamData = json_decode($response, true);
    
    if ($streamData && isset($streamData['success']) && $streamData['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'YouTube streaming started successfully',
            'stream_id' => $streamData['streamId'],
            'hls_url' => $streamData['hlsUrl'],
            'youtube_url' => $youtubeUrl,
            'stream_type' => $streamData['streamType'] ?? 'youtube',
            'full_hls_url' => 'http://localhost:8010' . $streamData['hlsUrl']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to start YouTube streaming',
            'error' => $streamData['error'] ?? 'Unknown error',
            'details' => $streamData
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}