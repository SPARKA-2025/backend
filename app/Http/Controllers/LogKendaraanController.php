<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\LogKendaraan;
use App\Models\Parkir;
use App\Models\Slot_Parkir;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogKendaraanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // ambil data log kendaraan, blok dan fakultas
        $logKendaraan = LogKendaraan::with('blok.fakultas')->get();

        // ambil data parkir dengan status slot 'Terisi' untuk laporan
        $parkirTerisi = \App\Models\Parkir::with(['slotParkir.blok.fakultas'])
            ->whereHas('slotParkir', function($query) {
                $query->where('status', 'Terisi');
            })
            ->get();

        // ambil data parkir khusus dengan status slot 'Terisi' untuk laporan
        $parkirKhususTerisi = \App\Models\ParkirKhusus::with(['slotParkir.blok.fakultas'])
            ->whereHas('slotParkir', function($query) {
                $query->where('status', 'Terisi');
            })
            ->get();

        // gabungkan data log kendaraan dengan data parkir terisi
        $combinedData = $logKendaraan->toArray();

        // tambahkan data parkir biasa yang terisi
        foreach ($parkirTerisi as $parkir) {
            $combinedData[] = [
                'id' => 'parkir_' . $parkir->id,
                'plat_nomor' => $parkir->plat_nomor,
                'created_at' => $parkir->waktu_booking, // capture time
                'exit_time' => null, // belum keluar
                'vehicle' => $parkir->jenis_mobil ?: 'Kendaraan Booking', // default jika kosong
                'image' => null, // tidak ada gambar untuk booking manual
                'blok' => $parkir->slotParkir->blok ?? null,
                'fakultas' => $parkir->slotParkir->blok->fakultas ?? null
            ];
        }

        // tambahkan data parkir khusus yang terisi
        foreach ($parkirKhususTerisi as $parkir) {
            $combinedData[] = [
                'id' => 'parkir_khusus_' . $parkir->id,
                'plat_nomor' => $parkir->plat_nomor,
                'created_at' => $parkir->waktu_booking, // capture time
                'exit_time' => null, // belum keluar
                'vehicle' => $parkir->jenis_mobil ?: 'Kendaraan Booking', // default jika kosong
                'image' => null, // tidak ada gambar untuk booking manual
                'blok' => $parkir->slotParkir->blok ?? null,
                'fakultas' => $parkir->slotParkir->blok->fakultas ?? null
            ];
        }

        return response()->json(['status' => 'success', 'data' => $combinedData], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'plat_nomor' => 'required|string',
            'vehicle' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1048',
            'exit_time' => 'nullable|date',
            'id_fakultas' => 'required|integer|exists:fakultas,id',
            'id_blok' => 'required|integer|exists:bloks,id'
        ]);

        // $logKendaraan = $request->only(['plat_nomor', 'capture_time', 'vehicle', 'location', 'image']);
        $logKendaraan = LogKendaraan::where('plat_nomor', $request->input('plat_nomor'))
                                    ->whereNotNull('capture_time')
                                    ->whereNull('exit_time')
                                    ->first();

        if ($logKendaraan) {
            //Pembaruan exit_time
            $logKendaraan->exit_time = Carbon::parse($request->input('exit_time'));

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageData = base64_encode(file_get_contents($image->getRealPath()));
                $logKendaraan->image = $imageData;
            }

            try {
                $logKendaraan->save();
                return response()->json([
                    'status' => 'success',
                    'pesan' => 'Data Berhasil Ditambahkan',
                    'data' => $logKendaraan
                ], Response::HTTP_CREATED);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Data Gagal Ditambahkan',
                    'data' => $e->getMessage()
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
             // Create new log kendaraan entry
             $newlogKendaraan = [
                 'plat_nomor' => $request->input('plat_nomor'),
                 'vehicle' => $request->input('vehicle'),
                 'capture_time' => $request->input('capture_time') ? Carbon::parse($request->input('capture_time')) : Carbon::now(),
                 'location' => $request->input('location', 'AI Detection'),
                 'exit_time' => null,
                 'id_fakultas' => 1, // Default fakultas
                 'id_blok' => 1 // Default blok
             ];

             if ($request->hasFile('image')) {
                 $image = $request->file('image');
                 $imageData = base64_encode(file_get_contents($image->getRealPath()));
                 $newlogKendaraan['image'] = $imageData;
             }

             try {
                 $data = LogKendaraan::create($newlogKendaraan);
                 return response()->json([
                     'status' => 'success',
                     'pesan' => 'Data Berhasil Ditambahkan',
                     'data' => $data
                 ], Response::HTTP_CREATED);
             } catch (\Exception $e) {
                 return response()->json([
                     'status' => 'error',
                     'pesan' => 'Data Gagal Ditambahkan',
                     'data' => $e->getMessage()
                 ], Response::HTTP_BAD_REQUEST);
             }
         }
    }

    /**
     * Create log kendaraan for AI service with flexible validation
     *
     * @return \Illuminate\Http\Response
     */
    public function createFromAI(Request $request)
    {
        $this->validate($request, [
            'plat_nomor' => 'required|string',
            'vehicle' => 'required|string',
            'capture_time' => 'nullable|date',
            'location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // $logKendaraan = $request->only(['plat_nomor', 'capture_time', 'vehicle', 'location', 'image']);
        $logKendaraan = LogKendaraan::where('plat_nomor', $request->input('plat_nomor'))
                                    ->whereNotNull('capture_time')
                                    ->whereNull('exit_time')
                                    ->first();

        if ($logKendaraan) {
            //Pembaruan exit_time
            $logKendaraan->exit_time = Carbon::parse($request->input('exit_time'));

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageData = base64_encode(file_get_contents($image->getRealPath()));
                $logKendaraan->image = $imageData;
            }

            try {
                $logKendaraan->save();
                return response()->json([
                    'status' => 'success',
                    'pesan' => 'Data Berhasil Ditambahkan',
                    'data' => $logKendaraan
                ], Response::HTTP_CREATED);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Data Gagal Ditambahkan',
                    'data' => $e->getMessage()
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            $newlogKendaraan = [
                'plat_nomor' => $request->input('plat_nomor'),
                'vehicle' => $request->input('vehicle'),
                'id_fakultas' => $request->input('id_fakultas', 1), // Default to 1 if not provided
                'id_blok' => $request->input('id_blok', 1), // Default to 1 if not provided
                'capture_time' => $request->input('capture_time') ? Carbon::parse($request->input('capture_time')) : Carbon::now(),
                'location' => $request->input('location', 'AI Detection'),
                'exit_time' => null
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageData = base64_encode(file_get_contents($image->getRealPath()));
                $newlogKendaraan['image'] = $imageData;
            }

            try {
                $data = LogKendaraan::create($newlogKendaraan);
                return response()->json([
                    'status' => 'success',
                    'pesan' => 'Data Berhasil Ditambahkan',
                    'data' => $data
                ], Response::HTTP_CREATED);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'pesan' => 'Data Gagal Ditambahkan',
                    'data' => $e->getMessage()
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    // public function exitTime(Request $request)
    // {
    //     $this->validate($request, [
    //         'plat_nomor' => 'required|string',
    //         'exit_time' => 'required|date',
    //         'vehicle' => 'required|string',
    //         'location' => 'required|string',
    //         'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1048'
    //     ]);

    //     // Cari data parkir berdasarkan plat_nomor dan status 'Dibooking'
    //     $parkir = Parkir::where('plat_nomor', $request->input('plat_nomor'))
    //                     ->where('status', 'Dibooking')
    //                     ->first();

    //     if (!$parkir) {
    //         return response()->json([
    //             'status' => 'error',
    //             'pesan' => 'Data parkir tidak ditemukan atau sudah terisi',
    //         ], Response::HTTP_BAD_REQUEST);
    //     }

    //     // Menentukan waktu keluar
    //     $waktuKeluar = Carbon::parse($request->input('exit_time'));

    //     // Ubah status slot parkir menjadi "Kosong"
    //     $slotParkir = Slot_Parkir::findOrFail($parkir->id_slot);
    //     $slotParkir->status = 'Kosong';
    //     $slotParkir->save();

    //     // Simpan data keluar kendaraan
    //     $logKendaraan = $request->only(['plat_nomor', 'exit_time', 'vehicle', 'location', 'image']);

    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $imageData = base64_encode(file_get_contents($image->getRealPath()));
    //         $logKendaraan['image'] = $imageData;
    //     }

    //     try {
    //         $data = LogKendaraan::create($logKendaraan);
    //         return response()->json([
    //             'status' => 'success',
    //             'pesan' => 'Data Berhasil Ditambahkan',
    //             'data' => $data
    //         ], Response::HTTP_CREATED);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'pesan' => 'Data Gagal Ditambahkan',
    //             'data' => $e->getMessage()
    //         ], Response::HTTP_BAD_REQUEST);
    //     }
    // }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LogKendaraan  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = LogKendaraan::with(['blok.fakultas'])->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        // Response Data
        $response = [
            'status' => 'success',
            'data' => [
                'id' => $data->id,
                'fakultas' => $data->fakultas->nama ?? null,
                'blok' => $data->blok->nama ?? null,
                'plat_nomor' => $data->plat_nomor,
                'capture_time' => $data->capture_time,
                'exit_time' => $data->exit_time,
                'vehicle' => $data->vehicle,
                'location' => $data->location,
                'image' => 'data:image/jpeg;base64,' . $data->image
            ]
        ];
        return response()->json([$response], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LogKendaraan  $logKendaraan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = LogKendaraan::with(['blok.fakultas'])->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $this->validate($request, [
            'id_fakultas' => 'sometimes|integer|exists:fakultas,id',
            'id_blok' => 'sometimes|integer|exists:bloks,id',
            'plat_nomor' => 'sometimes|required|string',
            'capture_time' => 'sometimes|required|date',
            'exit_time' => 'sometimes|required|date',
            'vehicle' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:1048'
        ]);

        $logKendaraan = $request->only(['id_fakultas', 'id_blok', 'plat_nomor', 'capture_time', 'vehicle', 'location', 'image']);

        if ($request->hasFile('image')) {
            $image = $request->files('image');
            $imageData = base64_encode(file_get_contents($image));
            $logKendaraan['image'] = $imageData;
        }
        
        $data->update($logKendaraan);

        // Response Data after update
        $response = [
            'status' => 'success',
            'data' => [
                'id' => $data->id,
                'fakultas' => $data->fakultas->nama_fakultas ?? null,
                'blok' => $data->blok->nama_blok ?? null,
                'plat_nomor' => $data->plat_nomor,
                'capture_time' => $data->capture_time,
                'exit_time' => $data->exit_time,
                'vehicle' => $data->vehicle,
                'location' => $data->location,
                'image' => 'data:image/jpeg;base64,' . $data->image
            ]
        ];

        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Diupdate', 'data' => $response], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LogKendaraan  $logKendaraan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = LogKendaraan::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);
    }
}
