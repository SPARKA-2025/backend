<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\cctvData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StreamingController extends Controller
{
    private $streamingApiUrl;
    
    public function __construct()
    {
        $this->streamingApiUrl = env('STREAMING_API_URL', 'http://localhost:8010');
    }
    
    /**
     * Start streaming for a CCTV camera
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function startStream(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'cctv_id' => 'required|integer|exists:cctv_data,id',
            ]);
            
            $cctvId = $request->input('cctv_id');
            
            // Get CCTV data from database
            $cctv = cctvData::find($cctvId);
            if (!$cctv) {
                return response()->json([
                    'success' => false,
                    'message' => 'CCTV not found'
                ], 404);
            }
            
            // Validate URL format
            if (empty($cctv->url) || !$this->isValidUrl($cctv->url)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or empty CCTV URL',
                    'url' => $cctv->url
                ], 400);
            }
            
            // Check stream type
            $streamType = $this->getStreamType($cctv->url);
            if ($streamType === 'unknown') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported URL format',
                    'current_url' => $cctv->url,
                    'supported_types' => ['RTSP', 'YouTube', 'HTTP/HTTPS', 'HLS (.m3u8)', 'MP4']
                ], 400);
            }
            
            Log::info("Processing stream for CCTV {$cctvId}", [
                'url' => $cctv->url,
                'stream_type' => $streamType
            ]);
            
            // For RTSP streams: convert to HLS for AI processing
            if ($streamType === 'rtsp') {
                // Check if stream is already active
                $cacheKey = "stream_active_{$cctvId}";
                $activeStream = Cache::get($cacheKey);
                
                if ($activeStream) {
                    return response()->json([
                        'success' => true,
                        'message' => 'RTSP stream already converted and active',
                        'stream_id' => $cctvId,
                        'hls_url' => $activeStream['hls_url'],
                        'original_url' => $cctv->url,
                        'stream_type' => 'rtsp_converted',
                        'requires_conversion' => true
                    ]);
                }
                
                // Start RTSP to HLS conversion via streaming server
                $response = Http::timeout(30)->post($this->streamingApiUrl . '/api/stream/start', [
                    'sourceUrl' => $cctv->url,
                    'cctvId' => $cctvId,
                    'streamType' => 'rtsp'
                ]);
                
                if ($response->successful()) {
                    $streamData = $response->json();
                    
                    // Cache stream info for 1 hour
                    Cache::put($cacheKey, [
                        'hls_url' => $streamData['hlsUrl'],
                        'stream_id' => $streamData['streamId'],
                        'started_at' => now()
                    ], 3600);
                    
                    // Update CCTV record with HLS URL
                    $cctv->update([
                        'hls_url' => $streamData['hlsUrl'],
                        'stream_active' => true,
                        'last_stream_start' => now()
                    ]);
                    
                    Log::info("RTSP stream converted for CCTV {$cctvId}", [
                        'rtsp_url' => $cctv->url,
                        'hls_url' => $streamData['hlsUrl']
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'RTSP stream converted to HLS successfully',
                        'stream_id' => $streamData['streamId'],
                        'hls_url' => $streamData['hlsUrl'],
                        'original_url' => $cctv->url,
                        'stream_type' => 'rtsp_converted',
                        'cctv_id' => $cctvId,
                        'requires_conversion' => true
                    ]);
                } else {
                    $errorMessage = 'Failed to convert RTSP stream';
                    $errorDetails = $response->body();
                    
                    Log::error("Failed to convert RTSP stream for CCTV {$cctvId}", [
                        'url' => $cctv->url,
                        'response_status' => $response->status(),
                        'response_body' => $errorDetails
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'error' => $errorDetails,
                        'status_code' => $response->status()
                    ], 500);
                }
            } else {
                // Check if stream is already active
                $cacheKey = "stream_active_{$cctvId}";
                $existingStream = Cache::get($cacheKey);
                if ($existingStream) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Stream is already active',
                        'stream_id' => $cctvId,
                        'streaming_url' => $existingStream['hls_url'],
                        'stream_type' => $existingStream['stream_type'],
                        'original_url' => $cctv->url,
                        'started_at' => $existingStream['started_at']
                    ]);
                }
                
                // For non-RTSP streams (HTTP, YouTube, etc.): use directly and trigger AI processing
                Log::info("Using direct stream for CCTV {$cctvId}", [
                    'url' => $cctv->url,
                    'stream_type' => $streamType
                ]);
                
                // Cache the active stream info
                Cache::put($cacheKey, [
                    'cctv_id' => $cctvId,
                    'rtsp_url' => $cctv->url,
                    'hls_url' => $cctv->url,
                    'started_at' => now(),
                    'stream_type' => $streamType
                ], 3600);
                
                // Update CCTV record to indicate direct streaming
                $cctv->update([
                    'hls_url' => null, // No HLS conversion needed
                    'stream_active' => true,
                    'last_stream_start' => now()
                ]);
                
                // For YouTube URLs, trigger AI processing automatically
                if ($streamType === 'youtube') {
                    $this->triggerAIProcessing($cctv->url, $cctvId, 'youtube');
                } else if (in_array($streamType, ['http', 'mp4'])) {
                    $this->triggerAIProcessing($cctv->url, $cctvId, 'http');
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Direct stream ready (no conversion needed)',
                    'stream_id' => $cctvId,
                    'streaming_url' => $cctv->url,
                    'original_url' => $cctv->url,
                    'stream_type' => $streamType,
                    'cctv_id' => $cctvId,
                    'requires_conversion' => false,
                    'ai_processing' => $streamType === 'youtube' ? 'triggered' : 'not_applicable'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Error processing stream for CCTV {$cctvId}: {$e->getMessage()}", [
                'cctv_id' => $cctvId ?? null,
                'url' => $cctv->url ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Stop streaming for a CCTV camera
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stopStream(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'cctv_id' => 'required|integer|exists:cctv_data,id',
            ]);
            
            $cctvId = $request->input('cctv_id');
            
            // Get stream info from cache
            $cacheKey = "stream_active_{$cctvId}";
            $activeStream = Cache::get($cacheKey);
            
            if (!$activeStream) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active stream found for this CCTV'
                ], 404);
            }
            
            // Stop streaming via streaming server
            $response = Http::timeout(30)->post($this->streamingApiUrl . '/api/stream/stop', [
                'streamId' => $activeStream['stream_id']
            ]);
            
            if ($response->successful()) {
                // Remove from cache
                Cache::forget($cacheKey);
                
                // Update CCTV record
                $cctv = cctvData::find($cctvId);
                $cctv->update([
                    'hls_url' => null,
                    'stream_active' => false,
                    'last_stream_stop' => now()
                ]);
                
                Log::info("Stream stopped for CCTV {$cctvId}");
                
                return response()->json([
                    'success' => true,
                    'message' => 'Stream stopped successfully',
                    'cctv_id' => $cctvId
                ]);
            } else {
                Log::error("Failed to stop stream for CCTV {$cctvId}", [
                    'response' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to stop stream',
                    'error' => $response->json()['error'] ?? 'Unknown error'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error("Error stopping stream: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get stream status for a CCTV camera
     * 
     * @param int $cctvId
     * @return JsonResponse
     */
    public function getStreamStatus(int $cctvId): JsonResponse
    {
        try {
            $cctv = cctvData::find($cctvId);
            if (!$cctv) {
                return response()->json([
                    'success' => false,
                    'message' => 'CCTV not found'
                ], 404);
            }
            
            $cacheKey = "stream_active_{$cctvId}";
            $activeStream = Cache::get($cacheKey);
            
            if ($activeStream) {
                // Verify stream is still active on streaming server
                $response = Http::timeout(10)->get($this->streamingApiUrl . '/api/stream/status/' . $activeStream['stream_id']);
                
                if ($response->successful()) {
                    $streamStatus = $response->json();
                    
                    return response()->json([
                        'success' => true,
                        'cctv_id' => $cctvId,
                        'stream_active' => true,
                        'hls_url' => $activeStream['hls_url'],
                        'rtsp_url' => $cctv->url,
                        'started_at' => $activeStream['started_at'],
                        'stream_status' => $streamStatus
                    ]);
                } else {
                    // Stream not found on server, clean up cache
                    Cache::forget($cacheKey);
                    $cctv->update(['stream_active' => false, 'hls_url' => null]);
                }
            }
            
            return response()->json([
                'success' => true,
                'cctv_id' => $cctvId,
                'stream_active' => false,
                'rtsp_url' => $cctv->url,
                'hls_url' => null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error getting stream status: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all active streams
     * 
     * @return JsonResponse
     */
    public function getActiveStreams(): JsonResponse
    {
        try {
            $response = Http::timeout(10)->get($this->streamingApiUrl . '/api/streams');
            
            if ($response->successful()) {
                $streamsData = $response->json();
                
                // Enrich with CCTV data
                $enrichedStreams = [];
                foreach ($streamsData['streams'] as $stream) {
                    $cctvId = $stream['streamId'];
                    $cctv = cctvData::find($cctvId);
                    
                    $enrichedStreams[] = [
                        'stream_id' => $stream['streamId'],
                        'cctv_id' => $cctvId,
                        'cctv_name' => $cctv ? "CCTV {$cctv->jenis_kamera} - Blok {$cctv->id_blok}" : 'Unknown',
                        'rtsp_url' => $stream['rtspUrl'],
                        'hls_url' => $stream['hlsUrl'],
                        'start_time' => $stream['startTime']
                    ];
                }
                
                return response()->json([
                    'success' => true,
                    'streams' => $enrichedStreams,
                    'count' => count($enrichedStreams)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get streams from streaming server'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error("Error getting active streams: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get streaming URL for frontend
     * 
     * @param int $cctvId
     * @return JsonResponse
     */
    public function getStreamingUrl(int $cctvId): JsonResponse
    {
        try {
            $cctv = cctvData::find($cctvId);
            if (!$cctv) {
                return response()->json([
                    'success' => false,
                    'message' => 'CCTV not found'
                ], 404);
            }
            
            $streamType = $this->getStreamType($cctv->url);
            
            // For RTSP streams: check if converted HLS is available
            if ($streamType === 'rtsp') {
                $cacheKey = "stream_active_{$cctvId}";
                $activeStream = Cache::get($cacheKey);
                
                if ($activeStream && $activeStream['hls_url']) {
                    return response()->json([
                        'success' => true,
                        'cctv_id' => $cctvId,
                        'streaming_url' => $activeStream['hls_url'],
                        'stream_type' => 'hls',
                        'original_url' => $cctv->url,
                        'original_stream_type' => 'rtsp',
                        'stream_active' => true,
                        'requires_conversion' => true,
                        'conversion_status' => 'converted'
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'cctv_id' => $cctvId,
                        'streaming_url' => null,
                        'stream_type' => 'rtsp',
                        'original_url' => $cctv->url,
                        'stream_active' => false,
                        'requires_conversion' => true,
                        'conversion_status' => 'not_converted',
                        'message' => 'RTSP stream needs to be started for conversion'
                    ]);
                }
            } else {
                // For non-RTSP streams: return original URL directly
                return response()->json([
                    'success' => true,
                    'cctv_id' => $cctvId,
                    'streaming_url' => $cctv->url,
                    'stream_type' => $streamType,
                    'original_url' => $cctv->url,
                    'stream_active' => true,
                    'requires_conversion' => false,
                    'conversion_status' => 'direct_stream',
                    'message' => 'Direct streaming (no conversion needed)'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Error getting streaming URL: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Trigger AI processing for a stream
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function triggerAI(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'cctv_id' => 'required|integer|exists:cctv_data,id',
                'stream_url' => 'required|string'
            ]);
            
            $cctvId = $request->input('cctv_id');
            $streamUrl = $request->input('stream_url');
            
            // Get CCTV data from database
            $cctv = cctvData::find($cctvId);
            if (!$cctv) {
                return response()->json([
                    'success' => false,
                    'message' => 'CCTV not found'
                ], 404);
            }
            
            Log::info("Triggering AI processing for CCTV {$cctvId}", [
                'stream_url' => $streamUrl,
                'cctv_name' => "CCTV {$cctv->jenis_kamera} - Blok {$cctv->id_blok}"
            ]);
            
            // Determine stream type and trigger AI processing
            $streamType = $this->getStreamType($streamUrl);
            $this->triggerAIProcessing($streamUrl, $cctvId, $streamType);
            
            return response()->json([
                'success' => true,
                'message' => 'AI processing triggered successfully',
                'cctv_id' => $cctvId,
                'stream_url' => $streamUrl,
                'cctv_info' => [
                    'name' => "CCTV {$cctv->jenis_kamera} - Blok {$cctv->id_blok}",
                    'location' => $cctv->id_blok,
                    'type' => $cctv->jenis_kamera
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error triggering AI processing: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if URL is RTSP
     * 
     * @param string $url
     * @return bool
     */
    private function isRtspUrl(string $url): bool
    {
        return str_starts_with(strtolower($url), 'rtsp://');
    }
    
    /**
     * Get stream type from URL
     * 
     * @param string $url
     * @return string
     */
    private function getStreamType(string $url): string
    {
        $url = strtolower($url);
        
        if (str_starts_with($url, 'rtsp://')) {
            return 'rtsp';
        } elseif (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        } elseif (str_contains($url, '.m3u8')) {
            return 'hls';
        } elseif (str_contains($url, '.mp4')) {
            return 'mp4';
        } elseif (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return 'http';
        }
        
        return 'unknown';
    }
    
    /**
     * Validate URL format
     * 
     * @param string $url
     * @return bool
     */
    private function isValidUrl(string $url): bool
    {
        // Check if URL is not empty and has valid format
        if (empty(trim($url))) {
            return false;
        }
        
        // Check for basic URL patterns
        $validPatterns = [
            '/^rtsp:\/\/.+/',
            '/^https?:\/\/.+/',
            '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/',
        ];
        
        foreach ($validPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        // Additional validation using filter_var for HTTP URLs
        if (str_starts_with(strtolower($url), 'http')) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }
        
        return false;
    }
    
    /**
     * Trigger AI processing for video streams
     * 
     * @param string $url
     * @param int $cctvId
     * @param string $streamType
     * @return void
     */
    private function triggerAIProcessing(string $url, int $cctvId, string $streamType): void
    {
        try {
            $integrationApiUrl = env('SPARKA_INTEGRATION_API_URL', 'http://localhost:5003');
            
            // Use the same RTSP processing endpoint for all stream types
            // YouTube URLs and HTTP streams are treated as RTSP streams
            $endpoint = '/process-rtsp';
            $payload = [
                'rtsp_url' => $url,
                'cctv_id' => $cctvId,
                'duration' => 3600, // Process for 1 hour for continuous monitoring
                'update_parking' => true
            ];
            
            // Make async call to avoid blocking the response
            Http::timeout(5)->post($integrationApiUrl . $endpoint, $payload);
            
            Log::info("AI processing triggered for CCTV {$cctvId}", [
                'url' => $url,
                'stream_type' => $streamType,
                'endpoint' => $endpoint,
                'duration' => 3600
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to trigger AI processing for CCTV {$cctvId}: {$e->getMessage()}", [
                'url' => $url,
                'stream_type' => $streamType
            ]);
        }
    }
}