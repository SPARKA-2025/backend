<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parkir;
use App\Models\ParkirKhusus;
use App\Models\Slot_Parkir;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class ParkirController extends Controller
{
    public function index()
    {
        // Mengambil data parkir yang berada di model Parkir
        $parkir = Parkir::with('slot_parkir.blok.fakultas')->get();

        // Mengambil data parkir yang berada di model Parkir Khusus
        $parkirKhusus = ParkirKhusus::with('slot_parkir.blok.fakultas')->get();

        if (!$parkir && !$parkirKhusus) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        // Kombinasikan data tersebut
        $combinedData = [
            'parkir' => $parkir,
            'parkir_khusus' => $parkirKhusus
        ];
        
        return response()->json(['status' => 'success', 'data' => $combinedData], Response::HTTP_OK);
    }

    public function getPesananUsers($id) {
        try {
            $parkirData = Parkir::where('id_user', $id)->get();

            if ($parkirData->isEmpty()) {
                return response()->json(['status' => 'error', 'pesan' => 'Data pesanan tidak ditemukan didalam id user ini'], Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'status' => 'success',
                'data' => $parkirData
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Terjadi saat kesalahan dalam mengambil data',
                'data' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function bookingSlot(Request $request)
    {
        // Validasi input
        $this->validate($request, [
            'plat_nomor' => 'nullable|string',
            'id_user' => 'required|integer|exists:users,id',
            // 'nama_pemesan' => 'required|string',
            'jenis_mobil' => 'required|string',
            'id_slot' => 'required|integer|exists:slot__parkirs,id',
        ]);

        // Ambil data user untuk mendapatkan plat nomor default
        $user = \App\Models\User::findOrFail($request->input('id_user'));
        
        // Gunakan plat nomor dari request, jika tidak ada gunakan dari profil user
        $platNomor = $request->input('plat_nomor') ?: $user->plat_nomor;
        
        // Validasi plat nomor harus ada (dari request atau profil)
        if (empty($platNomor)) {
            return response()->json(['status' => 'error', 'pesan' => 'Plat nomor harus diisi atau tersedia di profil user'], Response::HTTP_BAD_REQUEST);
        }
        
        // Validasi panjang plat nomor maksimal 10 karakter
        if (strlen($platNomor) > 10) {
            return response()->json(['status' => 'error', 'pesan' => 'Plat nomor tidak boleh lebih dari 10 karakter'], Response::HTTP_BAD_REQUEST);
        }

        // Memastikan slot parkir tersedia dan statusnya 'Kosong'
        $slotParkir = Slot_Parkir::findOrFail($request->input('id_slot'));

        if ($slotParkir->status == 'Terisi') {
            return response()->json(['status' => 'error', 'pesan' => 'Maaf, slot parkir yang Anda pilih sudah terisi'], Response::HTTP_BAD_REQUEST);
        }

        if ($slotParkir->status != 'Kosong') {
            return response()->json(['status' => 'error', 'pesan' => 'Slot parkir sudah dibooking atau terisi'], Response::HTTP_BAD_REQUEST);
        }

        // Menentukan waktu booking selama 1 jam
        $waktuBooking = Carbon::now();
        $waktuBookingBerakhir = $waktuBooking->copy()->addHour();

        // Ubah status slot parkir menjadi "Dibooking"
        $slotParkir->status = 'Dibooking';
        $slotParkir->save();

        // Simpan data booking ke dalam tabel parkir
        $parkirData = $request->only(['jenis_mobil','id_slot']);
        $parkirData['plat_nomor'] = $platNomor; // Gunakan plat nomor yang sudah diproses
        $parkirData['id_user'] = $request->input('id_user');
        $parkirData['waktu_booking'] = $waktuBooking;
        $parkirData['waktu_booking_berakhir'] = $waktuBookingBerakhir;

        try {
            $parkir = Parkir::create($parkirData);
            return response()->json([
                'status' => 'success',
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $parkir
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Gagal Ditambahkan',
                'data' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function bookingSlotKhusus(Request $request)
    {
        // Validasi input
        $this->validate($request, [
            'plat_nomor' => 'required|string|max:10',
            // 'id_admin',
            // 'nama_pemesan' => 'required|string',
            'jenis_mobil' => 'required|string',
            'id_slot' => 'required|integer|exists:slot__parkirs,id',
        ]);

        // Mendapatkan id_admin dari token JWT
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return response()->json(['status' => 'error', 'pesan' => 'Admin not found'], Response::HTTP_UNAUTHORIZED);
            }
            $id_admin = $admin->id;
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Unauthorized: Token is invalid or expired'], Response::HTTP_UNAUTHORIZED);
        }

        // Memastikan slot parkir tersedia dan statusnya 'Kosong'
        $slotParkir = Slot_Parkir::findOrFail($request->input('id_slot'));
        if ($slotParkir->status == 'Terisi') {
            return response()->json(['status' => 'error', 'pesan' => 'Maaf, slot parkir yang Anda pilih sudah terisi'], Response::HTTP_BAD_REQUEST);
        }

        if ($slotParkir->status != 'Kosong') {
            return response()->json(['status' => 'error', 'pesan' => 'Slot parkir sudah dibooking atau terisi'], Response::HTTP_BAD_REQUEST);
        }

        // Menentukan waktu booking selama 1 jam
        $waktuBooking = Carbon::now();
        $waktuBookingBerakhir = $waktuBooking->copy()->addHour();

        // Ubah status slot parkir menjadi "Dibooking"
        $slotParkir->status = 'Dibooking';
        $slotParkir->save();

        // Simpan data booking ke dalam tabel parkir
        $parkirData = $request->only(['plat_nomor', 'jenis_mobil','id_slot']);
        $parkirData['id_admin'] = $id_admin;
        $parkirData['waktu_booking'] = $waktuBooking;
        $parkirData['waktu_booking_berakhir'] = $waktuBookingBerakhir;

        try {
            $parkir = ParkirKhusus::create($parkirData);
            return response()->json([
                'status' => 'success',
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $parkir
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Gagal Ditambahkan',
                'data' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function batalBookingSlot($id_slot) {
        try {
            // Cari data slot parkir berdasarkan id slot
            $slotParkir = Slot_Parkir::findOrFail($id_slot);

            // Cari data pemesanan parkir berdasarkn id parkir
            $parkir = Parkir::where('id_slot', $id_slot)
            ->where('waktu_booking_berakhir', '>', Carbon::now())
            ->first();


            // Memeriksa apakah slot parkir sudah dibooking atau terisi
            if (!in_array($slotParkir->status, ['Dibooking', 'Terisi'])) {
                return response()->json(['status' => 'error', 'pesan' => 'slot parkir belum dibooking atau sudah dibatalkan'], Response::HTTP_BAD_REQUEST);
            }

            // Tandai booking sebagcai dibatalkan
            $parkir->waktu_booking_berakhir = '1970-01-01 00:00:00'; // Nilai khusus untuk menandai pembatalan
            $parkir->save();

            // Jika slot parkir sudah dibooking
            $slotParkir->status = 'Kosong';
            $slotParkir->save();

            return response()->json(['status' => 'success', 'pesan' => 'Booking berhasil dibatalkan dan slot parkir dikembalikan ke status Kosong'], Response::HTTP_OK);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['status' => 'error', 'pesan' => 'Data parkir atau slot parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan saat membatalkan booking', 'data' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    public function batalBookingSlotByParkirId($id) {
        try {
            // Cari data pemesanan parkir berdasarkan id parkir
            $parkir = Parkir::findOrFail($id);

            // Cari data slot parkir berdasarkan id slot
            $slotParkir = Slot_Parkir::findOrFail($parkir->id_slot);

            // Periksa apakah booking sudah dibatalkan sebelumnya
            if ($parkir->waktu_booking_berakhir == '1970-01-01 00:00:00') {
                return response()->json(['status' => 'error', 'pesan' => 'Booking sudah dibatalkan sebelumnya'], Response::HTTP_BAD_REQUEST);
            }

            // Memeriksa apakah slot parkir sudah dibooking atau terisi
            if (!in_array($slotParkir->status, ['Dibooking', 'Terisi'])) {
                return response()->json(['status' => 'error', 'pesan' => 'slot parkir belum dibooking atau sudah dibatalkan'], Response::HTTP_BAD_REQUEST);
            }

            // Tandai booking sebagai dibatalkan
            $parkir->waktu_booking_berakhir = '1970-01-01 00:00:00'; // Nilai khusus untuk menandai pembatalan
            $parkir->save();

            // Ubah status slot parkir menjadi kosong
            $slotParkir->status = 'Kosong';
            $slotParkir->save();

            return response()->json(['status' => 'success', 'pesan' => 'Booking berhasil dibatalkan dan slot parkir dikembalikan ke status Kosong'], Response::HTTP_OK);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['status' => 'error', 'pesan' => 'Data parkir atau slot parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan saat membatalkan booking', 'data' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    public function batalBookingSlotKhusus($id) {
        try {
            // Cari data pemesanan parkir berdasarkn id parkir
            $parkir = ParkirKhusus::findOrFail($id);

            // Cari data slot parkir berdasarkan id slot
            $slotParkir = Slot_Parkir::findOrFail($parkir->id_slot);

            // Ubah status slot parkir menjadi kosong
            $slotParkir->status = 'Kosong';
            $slotParkir->save();

            // Hapus data booking dari database
            $parkir->delete();

            return response()->json(['status' => 'success', 'pesan' => 'Booking berhasil dihapus dan slot parkir dikembalikan ke status Kosong'], Response::HTTP_OK);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['status' => 'error', 'pesan' => 'Data parkir atau slot parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan saat menghapus booking', 'data' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    public function ubahSlotKeKosong(Request $request)
    {
        $this->validate($request, [
            'plat_nomor' => 'required|string'
        ]);

        try {
            // Mencari data parkir umum berdasarkan plat nomor
            $parkir = Parkir::where('plat_nomor', $request->plat_nomor)
            ->where('waktu_booking_berakhir', '>', Carbon::now())
            ->first();

            // Mencari data parkir khusus berdasarkan plat nomor
            $parkirKhusus = ParkirKhusus::where('plat_nomor', $request->plat_nomor)
            ->where('waktu_booking_berakhir', '>', Carbon::now())
            ->first();

            // Pastikan salah satu data parkir ditemukan
            if (!$parkir && !$parkirKhusus) {
                return response()->json(['status' => 'error', 'pesan' => 'Data parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
            }

            // Mengambil slot parkir terkait jika data parkir umum ditemukan
            $slotParkir = $parkir ? Slot_Parkir::findOrFail($parkir->id_slot) : null;

            // Mengambil slot parkir terkait jika data parkir khusus ditemukan
            $slotParkirKhusus = $parkirKhusus ? Slot_Parkir::findOrFail($parkirKhusus->id_slot) : null;

            // Periksa apakah kedua slot (umum dan khusus) sudah kosong
            if (($slotParkir && $slotParkir->status == 'Kosong') && 
                ($slotParkirKhusus && $slotParkirKhusus->status == 'Kosong')) {
                return response()->json(['status' => 'error', 'pesan' => 'Status slot parkir sudah kosong'], Response::HTTP_BAD_REQUEST);
            }

            // Ubah status slot parkir umum menjadi "Kosong" jika ada dan belum kosong
            if ($slotParkir && $slotParkir->status != 'Kosong') {
                $slotParkir->status = 'Kosong';
                $slotParkir->save();
            }

            // Ubah status slot parkir khusus menjadi "Kosong" jika ada dan belum kosong
            if ($slotParkirKhusus && $slotParkirKhusus->status != 'Kosong') {
                $slotParkirKhusus->status = 'Kosong';
                $slotParkirKhusus->save();
            }

            return response()->json([
                'status' => 'success',
                'pesan' => 'Slot parkir berhasil diubah ke Kosong', 
                'data' => [
                    'slot_parkir' => $slotParkir, 
                    'slot_parkir_khusus' => $slotParkirKhusus
                ]
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Slot parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan saat mengubah status slot parkir', 'data' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function show($id)
    {
        $data = Parkir::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    public function showKhusus($id)
    {
        $data = ParkirKhusus::find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Parkir  $parkir
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
        // $parkir = Parkir::find($id);
        // if (!$parkir) {
        //     return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        // }

        // $this->validate($request, [
        //     'id_slot' => 'required|integer|exists:slot__parkirs,id',
        // ]);

        // $newSlotId = $request->input('id_slot');

        // // Check if the new slot is already occupied
        // $newSlot = Slot_Parkir::findOrFail($newSlotId);
        // if ($newSlot->status == 'Terisi') {
        //     return response()->json(['status' => 'error', 'pesan' => 'Maaf, slot parkir yang Anda pilih sudah terisi'], Response::HTTP_BAD_REQUEST);
        // }

        // // Update the old slot status to 'Kosong'
        // $oldSlot = $parkir->slot__parkir;
        // if ($oldSlot) {
        //     $oldSlot->status = 'Kosong';
        //     $oldSlot->save();
        // }

        // // Update the new slot status to 'Terisi'
        // $newSlot->status = 'Terisi';
        // $newSlot->save();

        // // Update the Parkir record with new slot ID
        // $parkir->update(['id_slot' => $newSlotId]);

        // return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Diupdate', 'data' => $parkir], Response::HTTP_OK);

        $parkirData = Parkir::find($id);
        if (!$parkirData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data pesanan parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }
        $parkirData->update($request->all());
        return response()->json(['status' => 'success', 'pesan' => 'Data berhasil diupdate', 'data' => $parkirData], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $parkir = Parkir::find($id);
        if (!$parkir) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $parkir->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);
    }

    /**
     * AI Integration endpoint to update parking status based on license plate detection
     * This endpoint is called by the AI service when a vehicle is detected
     */
    public function updateParkingStatusByPlate(Request $request)
    {
        $this->validate($request, [
            'plat_nomor' => 'required|string|max:10',
            'action' => 'required|string|in:entry,exit',
            'confidence' => 'nullable|numeric|min:0|max:1',
            'detection_time' => 'nullable|date',
            'location' => 'nullable|string',
            'camera_id' => 'nullable|string'
        ]);

        try {
            $platNomor = strtoupper(trim($request->plat_nomor));
            $action = $request->action;
            $confidence = $request->confidence ?? 0.8;
            $detectionTime = $request->detection_time ?? Carbon::now();
            $location = $request->location ?? 'Unknown';
            $cameraId = $request->camera_id ?? 'AI_CAMERA';

            // Log the detection for audit purposes
            \Log::info("AI Parking Detection", [
                'plate' => $platNomor,
                'action' => $action,
                'confidence' => $confidence,
                'time' => $detectionTime,
                'location' => $location,
                'camera' => $cameraId
            ]);

            if ($action === 'entry') {
                return $this->handleVehicleEntry($platNomor, $detectionTime, $location, $confidence);
            } elseif ($action === 'exit') {
                return $this->handleVehicleExit($platNomor, $detectionTime, $location, $confidence);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid action specified'
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            \Log::error("AI Parking Status Update Error", [
                'error' => $e->getMessage(),
                'plate' => $request->plat_nomor ?? 'unknown',
                'action' => $request->action ?? 'unknown'
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update parking status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle vehicle entry detection
     */
    private function handleVehicleEntry($platNomor, $detectionTime, $location, $confidence)
    {
        // Add 15 minutes grace period for bookings that just expired
        $gracePeriodTime = Carbon::now()->subMinutes(15);
        
        // Check if vehicle already has an active booking - check both regular and special parking
        // Include bookings that expired within the last 15 minutes
        $existingBooking = Parkir::where('plat_nomor', $platNomor)
            ->where('waktu_booking_berakhir', '>', $gracePeriodTime)
            ->first();

        $existingSpecialBooking = null;
        $isSpecialParking = false;
        
        // If no regular booking found, check special parking
        if (!$existingBooking) {
            $existingSpecialBooking = \App\Models\ParkirKhusus::where('plat_nomor', $platNomor)
                ->where('waktu_booking_berakhir', '>', $gracePeriodTime)
                ->first();
            
            if ($existingSpecialBooking) {
                $isSpecialParking = true;
            }
        }

        if ($existingBooking || $existingSpecialBooking) {
            // Vehicle has a booking, mark slot as occupied
            $bookingToUse = $isSpecialParking ? $existingSpecialBooking : $existingBooking;
            $slot = Slot_Parkir::find($bookingToUse->id_slot);
            
            \Log::info("Vehicle entry with existing booking", [
                'plate' => $platNomor,
                'booking_id' => $bookingToUse->id,
                'slot_id' => $bookingToUse->id_slot,
                'slot_found' => $slot ? 'yes' : 'no',
                'slot_status' => $slot ? $slot->status : 'null',
                'parking_type' => $isSpecialParking ? 'special' : 'regular'
            ]);
            
            if ($slot) {
                if ($slot->status === 'Dibooking' || $slot->status === 'Kosong') {
                    // Handle both 'Dibooking' and 'Kosong' status (in case booking expired and slot was reset)
                    $previousStatus = $slot->status;
                    $slot->status = 'Terisi';
                    $slot->save();

                    // Create LogKendaraan entry record
                    $logKendaraan = new \App\Models\LogKendaraan();
                    $logKendaraan->plat_nomor = $platNomor;
                    $logKendaraan->capture_time = $detectionTime;
                    $logKendaraan->vehicle = 'Unknown'; // Default vehicle type
                    $logKendaraan->id_fakultas = $slot->blok->id_fakultas ?? 1; // Default fakultas
                    $logKendaraan->id_blok = $slot->id_blok;
                    $logKendaraan->image = ''; // Default empty image
                    $logKendaraan->exit_time = null; // Entry record, no exit time yet
                    $logKendaraan->save();

                    $statusMessage = $previousStatus === 'Kosong' ? 'booking expired but within grace period' : 'booking confirmed';
                    
                    \Log::info("Slot status updated to Terisi and LogKendaraan entry created", [
                        'plate' => $platNomor,
                        'slot_id' => $slot->id,
                        'slot_name' => $slot->slot_name,
                        'previous_status' => $previousStatus === 'Kosong' ? 'Kosong (expired)' : 'Dibooking',
                        'log_kendaraan_id' => $logKendaraan->id
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'message' => "Vehicle entry recorded - {$statusMessage}",
                        'data' => [
                            'plate_number' => $platNomor,
                            'slot_id' => $slot->id,
                            'slot_name' => $slot->slot_name,
                            'action' => 'entry_confirmed',
                            'entry_time' => $detectionTime,
                            'parking_type' => $isSpecialParking ? 'special' : 'regular',
                            'confidence' => $confidence,
                            'log_kendaraan_id' => $logKendaraan->id,
                            'grace_period_used' => $previousStatus === 'Kosong'
                        ]
                    ], Response::HTTP_OK);
                } else {
                    // Check if slot is already Terisi but no LogKendaraan record exists
                    if ($slot->status === 'Terisi') {
                        // Check if LogKendaraan record already exists for this plate
                        $existingLogKendaraan = \App\Models\LogKendaraan::where('plat_nomor', $platNomor)
                            ->whereNull('exit_time')
                            ->orderBy('capture_time', 'desc')
                            ->first();
                            
                        if (!$existingLogKendaraan) {
                            // Create LogKendaraan entry record for already occupied slot
                            $logKendaraan = new \App\Models\LogKendaraan();
                            $logKendaraan->plat_nomor = $platNomor;
                            $logKendaraan->capture_time = $detectionTime;
                            $logKendaraan->vehicle = 'Unknown'; // Default vehicle type
                            $logKendaraan->id_fakultas = $slot->blok->id_fakultas ?? 1; // Default fakultas
                            $logKendaraan->id_blok = $slot->id_blok;
                            $logKendaraan->image = ''; // Default empty image
                            $logKendaraan->exit_time = null; // Entry record, no exit time yet
                            $logKendaraan->save();
                            
                            \Log::info("LogKendaraan entry created for already occupied slot", [
                                'plate' => $platNomor,
                                'slot_id' => $slot->id,
                                'slot_status' => $slot->status,
                                'log_kendaraan_id' => $logKendaraan->id
                            ]);
                            
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Vehicle entry recorded - slot already occupied',
                                'data' => [
                                    'plate_number' => $platNomor,
                                    'slot_id' => $slot->id,
                                    'slot_name' => $slot->slot_name,
                                    'current_status' => $slot->status,
                                    'action' => 'entry_recorded_occupied_slot',
                                    'entry_time' => $detectionTime,
                                    'parking_type' => $isSpecialParking ? 'special' : 'regular',
                                    'confidence' => $confidence,
                                    'log_kendaraan_id' => $logKendaraan->id
                                ]
                            ], Response::HTTP_OK);
                        } else {
                            \Log::info("LogKendaraan record already exists for occupied slot", [
                                'plate' => $platNomor,
                                'slot_id' => $slot->id,
                                'existing_log_id' => $existingLogKendaraan->id
                            ]);
                            
                            return response()->json([
                                'status' => 'info',
                                'message' => 'Vehicle already has active entry record',
                                'data' => [
                                    'plate_number' => $platNomor,
                                    'slot_id' => $slot->id,
                                    'slot_name' => $slot->slot_name,
                                    'current_status' => $slot->status,
                                    'action' => 'entry_already_recorded',
                                    'existing_log_id' => $existingLogKendaraan->id,
                                    'confidence' => $confidence
                                ]
                            ], Response::HTTP_OK);
                        }
                    } else {
                        \Log::warning("Slot status is not Dibooking or Terisi", [
                            'plate' => $platNomor,
                            'slot_id' => $slot->id,
                            'current_status' => $slot->status,
                            'expected_status' => 'Dibooking or Terisi'
                        ]);
                        
                        return response()->json([
                            'status' => 'warning',
                            'message' => "Slot status is {$slot->status}, expected Dibooking",
                            'data' => [
                                'plate_number' => $platNomor,
                                'slot_id' => $slot->id,
                                'slot_name' => $slot->slot_name,
                                'current_status' => $slot->status,
                                'action' => 'entry_status_mismatch',
                                'confidence' => $confidence
                            ]
                        ], Response::HTTP_OK);
                    }
                }
            } else {
                \Log::error("Slot not found for existing booking", [
                    'plate' => $platNomor,
                    'booking_id' => $bookingToUse->id,
                    'slot_id' => $bookingToUse->id_slot,
                    'parking_type' => $isSpecialParking ? 'special' : 'regular'
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Slot not found for existing booking',
                    'data' => [
                        'plate_number' => $platNomor,
                        'booking_id' => $bookingToUse->id,
                        'slot_id' => $bookingToUse->id_slot,
                        'action' => 'entry_slot_not_found',
                        'parking_type' => $isSpecialParking ? 'special' : 'regular',
                        'confidence' => $confidence
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // No existing booking - reject entry for vehicles without booking
        \Log::info("Vehicle entry denied - no booking found", [
            'plate' => $platNomor,
            'detection_time' => $detectionTime,
            'location' => $location,
            'confidence' => $confidence
        ]);
        
        return response()->json([
            'status' => 'warning',
            'message' => 'Vehicle entry denied - no active booking found',
            'data' => [
                'plate_number' => $platNomor,
                'action' => 'entry_denied',
                'reason' => 'no_booking',
                'confidence' => $confidence,
                'detection_time' => $detectionTime
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Handle vehicle exit detection
     */
    private function handleVehicleExit($platNomor, $detectionTime, $location, $confidence)
    {
        // First, check if there's an entry in LogKendaraan without exit_time
        $logKendaraan = \App\Models\LogKendaraan::where('plat_nomor', $platNomor)
            ->whereNull('exit_time')
            ->orderBy('capture_time', 'desc')
            ->first();
            
        if (!$logKendaraan) {
            // Check if vehicle has an active booking but no entry record yet
            $activeBooking = Parkir::where('plat_nomor', $platNomor)
                ->where('waktu_booking_berakhir', '>', Carbon::now())
                ->first();
                
            $activeSpecialBooking = \App\Models\ParkirKhusus::where('plat_nomor', $platNomor)
                ->where('waktu_booking_berakhir', '>', Carbon::now())
                ->first();
                
            if ($activeBooking || $activeSpecialBooking) {
                // Vehicle has booking but no entry record - this means it's still in booking phase
                \Log::info("Exit detected for vehicle with booking but no entry record", [
                    'plate' => $platNomor,
                    'has_regular_booking' => $activeBooking ? true : false,
                    'has_special_booking' => $activeSpecialBooking ? true : false
                ]);
                
                return response()->json([
                    'status' => 'info',
                    'message' => 'Vehicle has active booking but no entry record - ignoring exit detection',
                    'data' => [
                        'plate_number' => $platNomor,
                        'action' => 'exit_ignored_booking_only',
                        'reason' => 'no_entry_record_but_has_booking',
                        'confidence' => $confidence
                    ]
                ], Response::HTTP_OK);
            }
            
            return response()->json([
                'status' => 'warning',
                'message' => 'No entry record found for this vehicle or vehicle already exited',
                'data' => [
                    'plate_number' => $platNomor,
                    'action' => 'exit_ignored',
                    'reason' => 'no_entry_record',
                    'confidence' => $confidence
                ]
            ], Response::HTTP_OK);
        }

        // Find active parking session based on booking time - check both regular and special parking
        $activeParkingSession = Parkir::where('plat_nomor', $platNomor)
            ->where('waktu_booking_berakhir', '>', Carbon::now())
            ->first();

        $activeSpecialParkingSession = null;
        $isSpecialParking = false;
        
        // If no regular parking session found, check special parking
        if (!$activeParkingSession) {
            $activeSpecialParkingSession = \App\Models\ParkirKhusus::where('plat_nomor', $platNomor)
                ->where('waktu_booking_berakhir', '>', Carbon::now())
                ->first();
            
            if ($activeSpecialParkingSession) {
                $isSpecialParking = true;
            }
        }

        // Update exit_time in LogKendaraan table regardless of active session status
        $logKendaraan->exit_time = $detectionTime;
        $logKendaraan->save();
        
        \Log::info("LogKendaraan exit_time updated", [
            'plate' => $platNomor,
            'log_id' => $logKendaraan->id,
            'exit_time' => $detectionTime
        ]);

        // If no active parking session found, still record the exit but with different status
        if (!$activeParkingSession && !$activeSpecialParkingSession) {
            // Try to find the most recent parking session (even if expired)
            $recentParkingSession = Parkir::where('plat_nomor', $platNomor)
                ->orderBy('waktu_booking', 'desc')
                ->first();
                
            $recentSpecialParkingSession = \App\Models\ParkirKhusus::where('plat_nomor', $platNomor)
                ->orderBy('waktu_booking', 'desc')
                ->first();
                
            $mostRecentSession = null;
            $isRecentSpecial = false;
            
            if ($recentParkingSession && $recentSpecialParkingSession) {
                if ($recentSpecialParkingSession->waktu_booking > $recentParkingSession->waktu_booking) {
                    $mostRecentSession = $recentSpecialParkingSession;
                    $isRecentSpecial = true;
                } else {
                    $mostRecentSession = $recentParkingSession;
                }
            } elseif ($recentSpecialParkingSession) {
                $mostRecentSession = $recentSpecialParkingSession;
                $isRecentSpecial = true;
            } elseif ($recentParkingSession) {
                $mostRecentSession = $recentParkingSession;
            }
            
            if ($mostRecentSession) {
                // Update the expired session's end time and free the slot
                $mostRecentSession->waktu_booking_berakhir = $detectionTime;
                $mostRecentSession->save();
                
                $slot = Slot_Parkir::find($mostRecentSession->id_slot);
                if ($slot) {
                    $slot->status = 'Kosong';
                    $slot->save();
                }
                
                \Log::info("Expired parking session updated with exit", [
                    'plate' => $platNomor,
                    'parking_id' => $mostRecentSession->id,
                    'slot_id' => $slot ? $slot->id : null,
                    'exit_time' => $detectionTime,
                    'parking_type' => $isRecentSpecial ? 'special' : 'regular'
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Vehicle exit recorded - expired parking session completed',
                    'data' => [
                        'plate_number' => $platNomor,
                        'slot_id' => $slot ? $slot->id : null,
                        'slot_name' => $slot ? $slot->slot_name : null,
                        'action' => 'exit_completed',
                        'exit_time' => $detectionTime,
                        'parking_id' => $mostRecentSession->id,
                        'parking_type' => $isRecentSpecial ? 'special' : 'regular',
                        'session_status' => 'expired_but_completed',
                        'confidence' => $confidence
                    ]
                ], Response::HTTP_OK);
            }
            
            return response()->json([
                'status' => 'warning',
                'message' => 'Vehicle exit recorded but no parking session found',
                'data' => [
                    'plate_number' => $platNomor,
                    'action' => 'exit_recorded_no_session',
                    'reason' => 'no_parking_session',
                    'confidence' => $confidence,
                    'exit_time' => $detectionTime
                ]
            ], Response::HTTP_OK);
        }

        // Handle exit for the appropriate parking type
        if ($isSpecialParking) {
            // Handle special parking exit
            $activeSpecialParkingSession->waktu_booking_berakhir = $detectionTime;
            $activeSpecialParkingSession->save();
            
            $slot = Slot_Parkir::find($activeSpecialParkingSession->id_slot);
            if ($slot) {
                $slot->status = 'Kosong';
                $slot->save();
            }
            
            \Log::info("Special parking exit recorded", [
                'plate' => $platNomor,
                'parking_id' => $activeSpecialParkingSession->id,
                'slot_id' => $slot ? $slot->id : null,
                'exit_time' => $detectionTime
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle exit recorded - special parking session completed',
                'data' => [
                    'plate_number' => $platNomor,
                    'slot_id' => $slot ? $slot->id : null,
                    'slot_name' => $slot ? $slot->slot_name : null,
                    'action' => 'exit_completed',
                    'exit_time' => $detectionTime,
                    'parking_id' => $activeSpecialParkingSession->id,
                    'parking_type' => 'special',
                    'confidence' => $confidence,
                    'duration_minutes' => $activeSpecialParkingSession->waktu_booking ? 
                        Carbon::parse($activeSpecialParkingSession->waktu_booking)->diffInMinutes($detectionTime) : null
                ]
            ], Response::HTTP_OK);
        } else {
            // Handle regular parking exit
            $activeParkingSession->waktu_booking_berakhir = $detectionTime;
            $activeParkingSession->save();

            $slot = Slot_Parkir::find($activeParkingSession->id_slot);
            if ($slot) {
                $slot->status = 'Kosong';
                $slot->save();
            }
            
            \Log::info("Regular parking exit recorded", [
                'plate' => $platNomor,
                'parking_id' => $activeParkingSession->id,
                'slot_id' => $slot ? $slot->id : null,
                'exit_time' => $detectionTime
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle exit recorded - parking session completed',
                'data' => [
                    'plate_number' => $platNomor,
                    'slot_id' => $slot ? $slot->id : null,
                    'slot_name' => $slot ? $slot->slot_name : null,
                    'action' => 'exit_completed',
                    'exit_time' => $detectionTime,
                    'parking_id' => $activeParkingSession->id,
                    'parking_type' => 'regular',
                    'confidence' => $confidence,
                    'duration_minutes' => $activeParkingSession->waktu_booking ? 
                        Carbon::parse($activeParkingSession->waktu_booking)->diffInMinutes($detectionTime) : null
                ]
            ], Response::HTTP_OK);
        }
    }

    /**
     * Get parking statistics for AI monitoring
     */
    public function getParkingStats()
    {
        try {
            $totalSlots = Slot_Parkir::count();
            $occupiedSlots = Slot_Parkir::where('status', 'Terisi')->count();
            $bookedSlots = Slot_Parkir::where('status', 'Dibooking')->count();
            $availableSlots = Slot_Parkir::where('status', 'Kosong')->count();
            
            $activeSessions = Parkir::where('waktu_booking_berakhir', '>', Carbon::now())
                ->count();

            $todayEntries = Parkir::whereDate('waktu_booking', Carbon::today())->count();
            $todayExits = Parkir::whereDate('waktu_booking_berakhir', Carbon::today())
                ->where('waktu_booking_berakhir', '<', Carbon::now())
                ->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_slots' => $totalSlots,
                    'occupied_slots' => $occupiedSlots,
                    'booked_slots' => $bookedSlots,
                    'available_slots' => $availableSlots,
                    'occupancy_rate' => $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100, 2) : 0,
                    'active_sessions' => $activeSessions,
                    'today_entries' => $todayEntries,
                    'today_exits' => $todayExits,
                    'timestamp' => Carbon::now()->toISOString()
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve parking statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
