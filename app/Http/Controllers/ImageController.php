<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogKendaraan;
use App\Models\CaptureImage;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    /**
     * Display log kendaraan image
     */
    public function showLogKendaraanImage($id)
    {
        try {
            $logKendaraan = LogKendaraan::find($id);
            
            if (!$logKendaraan || !$logKendaraan->image) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            
            // Decode base64 image
            $imageData = base64_decode($logKendaraan->image);
            
            if ($imageData === false) {
                return response()->json(['error' => 'Invalid image data'], 400);
            }
            
            return response($imageData)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load image: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get log kendaraan image as base64
     */
    public function getLogKendaraanImageBase64($id)
    {
        try {
            $logKendaraan = LogKendaraan::find($id);
            
            if (!$logKendaraan || !$logKendaraan->image) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            
            return response()->json([
                'success' => true,
                'image' => 'data:image/jpeg;base64,' . $logKendaraan->image
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load image: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Display capture image
     */
    public function showCaptureImage($id)
    {
        try {
            $captureImage = CaptureImage::find($id);
            
            if (!$captureImage || !$captureImage->image_data) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            
            // Decode base64 image
            $imageData = base64_decode($captureImage->image_data);
            
            if ($imageData === false) {
                return response()->json(['error' => 'Invalid image data'], 400);
            }
            
            return response($imageData)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load image: ' . $e->getMessage()], 500);
        }
    }
}