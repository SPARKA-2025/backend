<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use Illuminate\Http\Request;
use App\Models\AccessibilityOperator;
use App\Models\Slot_Parkir;
use App\Models\LogKendaraan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FakultasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Fakultas::all();
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->validate($request,[
            'nama' => 'required|string',
            'deskripsi' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1048'
        ]);

        $fakultasData = $request->only('nama', 'deskripsi');

        // Proses convert data
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $fakultasData['image']=$imageData;
        }
        try{
            $fakultas = Fakultas::create($fakultasData);
            return response()->json([
                'status' => 'success',
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $fakultas
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
     * @param  \App\Models\Fakultas  $fakultas
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Fakultas::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fakultas  $fakultas
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Fakultas::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }
    
        // Ambil inputan kecuali gambar
        $updateData = $request->except('image');
    
        // Proses konversi gambar ke base64 jika ada gambar di request
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $base64Image = base64_encode(file_get_contents($image->getRealPath()));
            $updateData['image'] = $base64Image;
        }
    
        $data->update($updateData);
        return response()->json([
            'status' => 'success',
            'pesan' => 'Data Berhasil Diupdate',
            'data' => $data
        ], Response::HTTP_OK);
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fakultas  $fakultas
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     $data = Fakultas::find($id);
    //     if (!$data) {
    //         return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
    //     }

    //     $data->delete();
    //     return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);
    // }

    public function destroy($id)
    {
        $data = Fakultas::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        try {
            AccessibilityOperator::where('id_fakultas', $data->id)->delete();
            $data->delete();

            return response()->json(['status' => 'success', 'message' => 'Operator deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    /**
     * Get all fakultas with their slot statistics
     *
     * @return \Illuminate\Http\Response
     */
    public function indexWithStatistics()
    {
        $fakultasList = Fakultas::all();
        if (!$fakultasList) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $fakultasWithStats = $fakultasList->map(function ($fakultas) {
            $statistics = $this->getFakultasStatistics($fakultas->id);
            return [
                'id' => $fakultas->id,
                'nama' => $fakultas->nama,
                'deskripsi' => $fakultas->deskripsi,
                'image' => $fakultas->image,
                'created_at' => $fakultas->created_at,
                'updated_at' => $fakultas->updated_at,
                'statistics' => $statistics
            ];
        });

        return response()->json(['status' => 'success', 'data' => $fakultasWithStats], Response::HTTP_OK);
    }

    /**
     * Get statistics for a specific fakultas
     *
     * @param int $id_fakultas
     * @return array
     */
    private function getFakultasStatistics($id_fakultas)
    {
        try {
            // Simplified query to get slot statistics
            $totalSlots = DB::table('slot__parkirs')
                ->join('bloks', 'slot__parkirs.id_blok', '=', 'bloks.id')
                ->where('bloks.id_fakultas', $id_fakultas)
                ->count();

            $availableSlots = DB::table('slot__parkirs')
                ->join('bloks', 'slot__parkirs.id_blok', '=', 'bloks.id')
                ->where('bloks.id_fakultas', $id_fakultas)
                ->where('slot__parkirs.status', 'Kosong')
                ->count();

            $slotsDibookingCount = DB::table('slot__parkirs')
                ->join('bloks', 'slot__parkirs.id_blok', '=', 'bloks.id')
                ->where('bloks.id_fakultas', $id_fakultas)
                ->where('slot__parkirs.status', 'Dibooking')
                ->count();

            $slotsTerisiCount = DB::table('slot__parkirs')
                ->join('bloks', 'slot__parkirs.id_blok', '=', 'bloks.id')
                ->where('bloks.id_fakultas', $id_fakultas)
                ->where('slot__parkirs.status', 'Terisi')
                ->count();

            // Get completed slots from log
            $slotSelesai = LogKendaraan::join('bloks', 'log_kendaraans.id_blok', '=', 'bloks.id')
                ->where('bloks.id_fakultas', $id_fakultas)
                ->whereNotNull('log_kendaraans.exit_time')
                ->count();

            return [
                'total' => $totalSlots,
                'kosong' => $availableSlots,
                'terisi' => $slotsTerisiCount,
                'dibooking' => $slotsDibookingCount,
                'selesai' => $slotSelesai
            ];
        } catch (\Exception $e) {
            // Return default statistics if error occurs
            return [
                'total' => 0,
                'kosong' => 0,
                'terisi' => 0,
                'dibooking' => 0,
                'selesai' => 0
            ];
        }
    }
}
