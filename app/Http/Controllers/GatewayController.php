<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class GatewayController extends Controller
{
    public function getGatewayOnPart($id_part)
    {
        $part = Part::find($id_part);
        if (!$part) {
            return response()->json(['status' => 'error', 'pesan' => 'Part tidak ditemukan'], 404);
        }

        $latestGateways = DB::table('gateways as gw1')
            ->select('gw1.id', 'gw1.gateway_name', 'gw1.x', 'gw1.y', 'gw1.direction', 'gw1.id_part', 'gw1.created_at')
            ->where('gw1.id_part', '=', $id_part)
            ->orderBy('gw1.id') 
            ->get();

        return response()->json([
            'part' => $part->nama,
            'gateways' => $latestGateways
        ]);
    }

    public function createForAll(Request $request, $id_part, $id_blok)
    {
        // Enhanced debug logging
        \Log::info('GatewayController createForAll called', [
            'request_data' => $request->all(),
            'has_gateways' => $request->has('gateways'),
            'gateways_value' => $request->get('gateways'),
            'gateways_count' => count($request->get('gateways', [])),
            'raw_input' => $request->getContent(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        // Log to file for debugging
        file_put_contents(storage_path('logs/gateway_debug.log'), 
            date('Y-m-d H:i:s') . " - Gateway Create Request:\n" . 
            json_encode($request->all(), JSON_PRETTY_PRINT) . "\n\n", 
            FILE_APPEND | LOCK_EX
        );

        $this->validate($request, [
            'gateways' => 'sometimes|array', 
            'gateways.*.gateway_name' => 'sometimes|string',
            'gateways.*.x' => 'sometimes|required',
            'gateways.*.y' => 'sometimes|required',
            'gateways.*.direction' => 'sometimes|string',
            'gateways.*.position' => 'sometimes|string',
        ]);        

        $gateways = $request->get('gateways', []);
        $createdGateways = [];

        // If no gateways to create, return success
        if (empty($gateways)) {
            return response()->json([
                'status' => 'success',
                'pesan' => 'Tidak ada data gateway untuk ditambahkan',
                'data' => []
            ], Response::HTTP_OK);
        }

        try {
            foreach ($gateways as $index => $gtwy) {
                $direction = $gtwy['direction'] ?? ($gtwy['position'] ?? 'x');
                $gtwyData = [
                    'gateway_name' => $gtwy['gateway_name'] ?? 'Gateway ' . ($index + 1), 
                    'direction' => $direction,
                    'x' => (string)$gtwy['x'],
                    'y' => (string)$gtwy['y'],
                    'id_part' => $id_part,
                    'id_blok' => $id_blok
                ];
                $createdGateways[] = Gateway::create($gtwyData);
            }

            return response()->json([
                'status' => 'success',
                'pesan' => 'Semua data berhasil ditambahkan',
                'data' => $createdGateways
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateForAll(Request $request)
    {
        // Debug logging
        \Log::info('GatewayController updateForAll called', [
            'request_data' => $request->all(),
            'has_gateways' => $request->has('gateways'),
            'gateways_value' => $request->get('gateways')
        ]);

        $this->validate($request, [
            'gateways' => 'sometimes|array',
            'gateways.*.id' => 'sometimes|required|exists:gateways,id',
            'gateways.*.gateway_name' => 'sometimes|string',
            'gateways.*.x' => 'sometimes',
            'gateways.*.y' => 'sometimes',
            'gateways.*.direction' => 'sometimes|string',
            'gateways.*.position' => 'sometimes|string',
        ]);

        $gateways = $request->get('gateways', []);
        $updatedGateways = [];

        // If no gateways to update, return success
        if (empty($gateways)) {
            return response()->json([
                'status' => 'success',
                'pesan' => 'Tidak ada data gateway untuk diperbarui',
                'data' => []
            ], Response::HTTP_OK);
        }

        try {
            
            foreach ($gateways as $gtwy) {
                $gtwyData = Gateway::where('id', $gtwy['id'])
                    ->first();

                if ($gtwyData) {
                    $updateData = [];
                    if (isset($gtwy['gateway_name'])) $updateData['gateway_name'] = $gtwy['gateway_name'];
                    if (isset($gtwy['x'])) $updateData['x'] = (string)$gtwy['x'];
                    if (isset($gtwy['y'])) $updateData['y'] = (string)$gtwy['y'];
                    if (isset($gtwy['direction'])) $updateData['direction'] = $gtwy['direction'];
                    if (isset($gtwy['position'])) $updateData['direction'] = $gtwy['position'];
                    
                    $gtwyData->update($updateData);
                    $updatedGateways[] = $gtwyData;
                }
            }

            return response()->json([
                'status' => 'success',
                'pesan' => 'Semua data berhasil diperbarui',
                'data' => $updatedGateways
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroyForMany(Request $request)
    {

        $this->validate($request, [
            'ids' => 'required|array', 
            'ids.*' => 'required|exists:gateways,id' 
        ]);

        $ids = $request->get('ids');

        try {
            $deletedCount = Gateway::whereIn('id', $ids)
                ->delete();

            return response()->json([
                'status' => 'success',
                'pesan' => "{$deletedCount} data berhasil dihapus",
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
