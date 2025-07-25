<?php

namespace App\Http\Controllers;

use App\Models\Blok;
use App\Models\Part;
use App\Models\cctvData;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CctvDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($fakultasId, $blokId)
    {
        // Check if the Fakultas exists
        // $fakultas = Fakultas::find($fakultasId);
        // if (!$fakultas) {
        //     return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        // }

        // Check if the Blok exists within the specified Fakultas
        $blok = Blok::where('id', $blokId)->where('id_fakultas', $fakultasId)->first();
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        // Retrieve CCTV data associated with the specified Blok and Fakultas
        $data = $blok->cctvData()->where('id_fakultas', $fakultasId)->get();

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $fakultasId, $blokId)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $blok = Blok::where('id', $blokId)->where('id_fakultas', $fakultasId)->first();
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }
        $this->validate($request,[
            'jenis_kamera' => 'required|string',
            'url' => 'required|string',
            'x' => 'required|string',
            'y' => 'required|string',
            'offset_x' => 'string',
            'offset_y' => 'string',
            'angle' => 'required|string'
            // 'id_slot' => 'required|integer|exists:slot__parkirs,id'
        ]);

        $cctvData = $request->only('jenis_kamera', 'url', 'x', 'y', 'angle', 'offset_x', 'offset_y');
        $cctvData ['id_fakultas'] = $fakultas->id;
        $cctvData ['id_blok'] = $blok->id;
        
        try{
            $cctv = cctvData::create($cctvData);
            return response()->json([
                'status' => 'success',
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $cctv
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Gagal Ditambahkan',
                'data' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cctvData  $cctvData
     * @return \Illuminate\Http\Response
     */
    public function show($fakultasId, $blokId, $id)
    {
        // $fakultas = Fakultas::find($fakultasId);
        // if (!$fakultas) {
        //     return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        // }

        $blok = Blok::where('id', $blokId)->where('id_fakultas', $fakultasId)->first();
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $cctvData = cctvData::where('id', $id)->where('id_fakultas', $fakultasId)->where('id_blok', $blokId)->first();
        if (!$cctvData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $cctvData], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cctvData  $cctvData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $fakultasId, $blokId, $id)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $blok = Blok::where('id', $blokId)->where('id_fakultas', $fakultasId)->first();
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $cctvData = cctvData::find($id);
        if (!$cctvData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        // if ($request->has('id_fakultas')) {
        //     $fakultas = Fakultas::find($request->input('id_fakultas'));
        //     if(!$fakultas) {
        //         return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        //     }
        // }

        // if ($request->has('id_blok')) {
        //     $blok = Blok::where('id', $request->input('id_blok'))->where('id_fakultas', $request->input('id_fakultas'))->first();
        //     if (!$blok) {
        //         return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        //     }
        // }

        $cctvData->update($request->all());
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil diupdate', 'data' => $cctvData], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cctvData  $cctvData
     * @return \Illuminate\Http\Response
     */
    public function destroy($fakultasId, $blokId, $id)
    {
        $fakultas = Fakultas::find($fakultasId);
        if (!$fakultas) {
            return response()->json(['status' => 'error', 'pesan' => 'Data fakultas tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $blok = Blok::where('id', $blokId)->where('id_fakultas', $fakultasId)->first();
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $cctvData = cctvData::where('id', $id)->where('id_fakultas', $fakultasId)->where('id_blok', $blokId)->first();
        if (!$cctvData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $cctvData->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);
    }

    public function getCctvOnPart($id_part)
    {
        $part = Part::find($id_part);
        if (!$part) {
            return response()->json(['status' => 'error', 'pesan' => 'Part tidak ditemukan'], 404);
        }

        $latestCctvs = DB::table('cctv_data as ct1')
            ->select('ct1.id', 'ct1.jenis_kamera', 'ct1.angle', 'ct1.url', 'ct1.x', 'ct1.y', 'ct1.offset_x', 'ct1.offset_y', 'ct1.id_fakultas', 'ct1.id_blok', 'ct1.id_part', 'ct1.created_at')
            ->where('ct1.id_part', '=', $id_part)
            ->orderBy('ct1.id') 
            ->get();

        return response()->json([
            'part' => $part->nama,
            'cctvs' => $latestCctvs
        ]);
    }

    public function createForAll(Request $request, $id_part, $id_blok, $id_fakultas)
    {
        \Log::info('CctvDataController createForAll called', [
            'request_data' => $request->all(),
            'id_part' => $id_part,
            'id_blok' => $id_blok,
            'id_fakultas' => $id_fakultas
        ]);

        $this->validate($request, [
            'cctvs' => 'sometimes|array',
            'cctvs.*.jenis_kamera' => 'sometimes|string',
            'cctvs.*.x' => 'sometimes|required|string',
            'cctvs.*.y' => 'sometimes|required|string',
            'cctvs.*.offset_x' => 'sometimes|string',
            'cctvs.*.offset_y' => 'sometimes|string',
            'cctvs.*.angle' => 'sometimes|string',
            'cctvs.*.url' => 'sometimes|string'
        ]);
        

        $cctvs = $request->get('cctvs', []);
        $createdCctvs = [];

        // If no cctvs to create, return success
        if (empty($cctvs)) {
            return response()->json([
                'status' => 'success',
                'pesan' => 'Tidak ada data CCTV untuk ditambahkan',
                'data' => []
            ], Response::HTTP_OK);
        }

        try {
            foreach ($cctvs as $cctv) {
                $cctvData = [
                    'jenis_kamera' => $cctv['jenis_kamera'] ?? 'Default Camera',
                    'angle' => $cctv['angle'] ?? '0',
                    'url' => $cctv['url'] ?? '',
                    'x' => $cctv['x'],
                    'y' => $cctv['y'],
                    'offset_x' => $cctv['offset_x'] ?? '0',
                    'offset_y' => $cctv['offset_y'] ?? '0',
                    'id_blok' => $id_blok,
                    'id_part' => $id_part,
                    'id_fakultas' => $id_fakultas
                ];
                $createdCctvs[] = cctvData::create($cctvData);
            }

            return response()->json([
                'status' => 'success',
                'pesan' => 'Semua data berhasil ditambahkan',
                'data' => $createdCctvs
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

        $this->validate($request, [
            'cctvs' => 'sometimes|array', 
            'cctvs.*.id' => 'required|exists:cctv_data,id',
            'cctvs.*.x' => 'sometimes|required|string',
            'cctvs.*.y' => 'sometimes|required|string',
            'cctvs.*.offset_x' => 'sometimes|required|string',
            'cctvs.*.offset_y' => 'sometimes|required|string',
            'cctvs.*.angle' => 'sometimes|required|string',
            'cctvs.*.url' => 'sometimes|required|string'
        ]);

        $cctvs = $request->get('cctvs', []);
        $updatedCctvs = [];

        // If no cctvs to update, return success
        if (empty($cctvs)) {
            return response()->json([
                'status' => 'success',
                'pesan' => 'Tidak ada data CCTV untuk diperbarui',
                'data' => []
            ], Response::HTTP_OK);
        }

        try {
            foreach ($cctvs as $cctv) {
                $cctvData = cctvData::where('id', $cctv['id'])
                    ->first();

                if ($cctvData) {
                    $cctvData->update($cctv);
                    $updatedCctvs[] = $cctvData;
                }
            }

            return response()->json([
                'status' => 'success',
                'pesan' => 'Semua data berhasil diperbarui',
                'data' => $updatedCctvs
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
            'ids.*' => 'required|exists:cctv_data,id' 
        ]);

        $ids = $request->get('ids');

        try {
            $deletedCount = cctvData::whereIn('id', $ids)
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