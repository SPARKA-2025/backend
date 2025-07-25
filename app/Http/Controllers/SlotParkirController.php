<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Blok;
use App\Models\Part;
use App\Models\Parkir;
use App\Models\Slot_Parkir;
use App\Models\LogKendaraan;
use App\Models\ParkirKhusus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SlotParkirController extends Controller
{
    public function index()
    {
        $this->updateAndGetExpiredBookings();
        // $this->updateAndGetExpiredBookingsKhusus();
        // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
        $latestSlotsQuery = DB::table('slot__parkirs as sp1')
            ->select('sp1.id', 'sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.created_at')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
                            FROM slot__parkirs
                            GROUP BY slot_name, id_blok) as sp2'), function($join) {
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                    ->on('sp1.id_blok', '=', 'sp2.id_blok')
                    ->on('sp1.created_at', '=', 'sp2.max_created_at');
            })
            // ->where('sp1.id_blok', '=', $id_blok)
            ->distinct();

        $latestSlots = $latestSlotsQuery->get();

        // Data untuk total slot dan slot kosong
        $availableSlots = $latestSlots->where('status', 'Kosong')->count();
        $totalSlots = $latestSlots->count();
        $slotKosong = $availableSlots . '/' . $totalSlots;

        // Data untuk slot di Blok 1
        // $slotsBlok1Count = $latestSlotsQuery->where('sp1.id_blok', '=', 1)->count();

        // Data untuk slot yang dibooking
        // $slotsDibookingQuery = $latestSlotsQuery
        //     ->leftJoin('parkirs as parkir', 'sp1.id', '=', 'parkir.id_slot')
        //     ->leftJoin('parkir_khususes as parkir_khusus', 'sp1.id', '=', 'parkir_khusus.id_slot')
        //     ->where('sp1.status', 'Dibooking')
        //     ->where(function($query) {
        //         $query->where(function($q) {
        //             $q->whereNotNull('parkir.id')
        //                 ->where('parkir.waktu_booking_berakhir', '>', Carbon::now());
        //         })->orWhere(function($q){
        //             $q->whereNotNull('parkir_khusus.id')
        //                 ->where('parkir_khusus.waktu_booking_berakhir', '>', Carbon::now());
        //         });
        //     });

        // $slotsDibookingKhususQuery = $latestSlotsQuery
            

        // Hitung jumlah slot yang dibooking
        $slotsDibookingCount = $latestSlots->where('status', 'Dibooking')->count();

        // Hitung jumlah slot terisi
        $slotsTerisiCount = $latestSlots->where('status', 'Terisi')->count();

        // Hitung jumlah slot selesai berdasarkan log kendaraan
        $slotSelesai = LogKendaraan::whereNotNull('exit_time')->count();

        return response()->json([
            'total_slot' => $totalSlots,
            'slot_kosong' => $slotKosong,
            'slot_selesai' => $slotSelesai,
            'slots_dibooking' => $slotsDibookingCount,
            'slot_terisi' => $slotsTerisiCount
        ]);
    }

    public function index2($id_blok)
    {
        $this->updateAndGetExpiredBookings();
        // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
        $latestSlotsQuery = DB::table('slot__parkirs as sp1')
            ->select('sp1.id', 'sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.created_at')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
                            FROM slot__parkirs
                            GROUP BY slot_name, id_blok) as sp2'), function($join) {
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                    ->on('sp1.id_blok', '=', 'sp2.id_blok')
                    ->on('sp1.created_at', '=', 'sp2.max_created_at');
            })
            ->where('sp1.id_blok', '=', $id_blok)
            ->distinct();

        $latestSlots = $latestSlotsQuery->get();

        // Data untuk total slot dan slot kosong
        $availableSlots = $latestSlots->where('status', 'Kosong')->count();
        $totalSlots = $latestSlots->count();
        $slotKosong = $availableSlots . '/' . $totalSlots;

        // Data untuk slot di Blok 1
        // $slotsBlok1Count = $latestSlotsQuery->where('sp1.id_blok', '=', 1)->count();

        // Data untuk slot yang dibooking
        $slotsDibookingCount = $latestSlotsQuery
            ->leftJoin('parkirs as parkir', 'sp1.id', '=', 'parkir.id_slot')
            ->where('sp1.status', 'Dibooking')
            ->count();

        // Hitung jumlah slot terisi
        $slotsTerisiCount = $latestSlots->where('status', 'Terisi')->count();

        // Hitung jumlah slot selesai berdasarkan log kendaraan
        $slotSelesai = LogKendaraan::whereNotNull('exit_time')->count();

        return response()->json([
            'total_slot' => $totalSlots,
            'slot_kosong' => $slotKosong,
            'slot_selesai' => $slotSelesai,
            'slots_dibooking' => $slotsDibookingCount,
            'slot_terisi' => $slotsTerisiCount
        ]);
    }

    public function getSlotKosongDanTotal($id_blok)
    {
        $this->updateAndGetExpiredBookings();
        // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
        $latestSlotsQuery = DB::table('slot__parkirs as sp1')
            ->select('sp1.id', 'sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.created_at')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
                            FROM slot__parkirs
                            GROUP BY slot_name, id_blok) as sp2'), function($join) {
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                    ->on('sp1.id_blok', '=', 'sp2.id_blok')
                    ->on('sp1.created_at', '=', 'sp2.max_created_at');
            })
            ->where('sp1.id_blok', '=', $id_blok)
            ->distinct();

        $latestSlots = $latestSlotsQuery->get();

        // Data untuk total slot dan slot kosong
        $availableSlots = $latestSlots->where('status', 'Kosong')->count();
        $totalSlots = $latestSlots->count();
        $slotKosong = $availableSlots . '/' . $totalSlots;

        // Data untuk slot di Blok 1
        // $slotsBlok1Count = $latestSlotsQuery->where('sp1.id_blok', '=', 1)->count();

        // // Data untuk slot yang dibooking
        // $slotsDibookingCount = $latestSlotsQuery
        //     ->leftJoin('parkirs as parkir', 'sp1.id', '=', 'parkir.id_slot')
        //     ->where('sp1.status', 'Dibooking')
        //     ->count();

        // // Hitung jumlah slot terisi
        // $slotsTerisiCount = $latestSlots->where('status', 'Terisi')->count();

        // // Hitung jumlah slot selesai berdasarkan log kendaraan
        // $slotSelesai = LogKendaraan::whereNotNull('exit_time')->count();

        return response()->json([
            'total_slot' => $totalSlots,
            'slot_kosong' => $slotKosong,
            // 'slot_selesai' => $slotSelesai,
            // 'slots_dibooking' => $slotsDibookingCount,
            // 'slot_terisi' => $slotsTerisiCount
        ]);
    }

    public function getIdblokStatusSlotname()
    {
        // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
        $latestSlots = DB::table('slot__parkirs as sp1')
            ->select('sp1.id', 'sp1.slot_name', 'sp1.status', 'sp1.id_blok')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
                            FROM slot__parkirs
                            GROUP BY slot_name, id_blok) as sp2'), function($join) {
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                    ->on('sp1.id_blok', '=', 'sp2.id_blok')
                    ->on('sp1.created_at', '=', 'sp2.max_created_at');
            })
            ->distinct()
            ->get();

        return response()->json($latestSlots);
    }

    public function getSlotInBlok($id_blok)
    {
        $this->updateAndGetExpiredBookings();
        $blok = Blok::find($id_blok);
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $blok->slotParkirs()->with('blok')->get();
        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    public function getSlotDepanBelakang($id_blok)
    {
        // $blok = Blok::find($id_blok);
        // if (!$blok) {
        //     return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        // }
        // Pre-defined order for slots
        $slotOrder = [
            'Depan' => [
                ['slot_name' => '1'], ['slot_name' => '2'], ['slot_name' => '3'], ['slot_name' => '4'],
                ['slot_name' => '5'], ['slot_name' => '6'], ['slot_name' => '7'], ['slot_name' => '8'],
                ['slot_name' => '9'], ['slot_name' => '10'], ['slot_name' => '11'], ['slot_name' => '12'],
                ['slot_name' => '13'], ['slot_name' => '14'], ['slot_name' => '15'], ['slot_name' => '16'],
                ['slot_name' => '17'], ['slot_name' => '18'], ['slot_name' => '19'], ['slot_name' => '20'],
                ['slot_name' => '21'], ['slot_name' => '22'], ['slot_name' => '23'], ['slot_name' => '24'],
                ['slot_name' => '25'], ['slot_name' => '26'], ['slot_name' => '27'], ['slot_name' => '28']
            ],
            'Belakang' => [
                ['slot_name' => '29'], ['slot_name' => '30'], ['slot_name' => '31'], ['slot_name' => '32'],
                ['slot_name' => '33'], ['slot_name' => '34'], ['slot_name' => '35'], ['slot_name' => '36'],
                ['slot_name' => '37'], ['slot_name' => '38'], ['slot_name' => '39'], ['slot_name' => '40'],
                ['slot_name' => '41'], ['slot_name' => '42'], ['slot_name' => '43'], ['slot_name' => '44'],
                ['slot_name' => '45'], ['slot_name' => '46'], ['slot_name' => '47'], ['slot_name' => '48'],
                ['slot_name' => '49'], ['slot_name' => '50']
            ]
        ];

        // Create an array of slot names to use in the query
        $slotNames = array_merge(
            array_column($slotOrder['Depan'], 'slot_name'),
            array_column($slotOrder['Belakang'], 'slot_name')
        );

        // Retrieve the latest slots from the database
        $latestSlots = DB::table('slot__parkirs as sp1')
            ->select('sp1.id', 'sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.created_at')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
                            FROM slot__parkirs
                            GROUP BY slot_name, id_blok) as sp2'), function($join) {
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                    ->on('sp1.id_blok', '=', 'sp2.id_blok')
                    ->on('sp1.created_at', '=', 'sp2.max_created_at');
            })
            ->where('sp1.id_blok', '=', $id_blok)
            ->whereIn('sp1.slot_name', $slotNames)
            ->orderByRaw("FIELD(sp1.slot_name, " . implode(',', $slotNames) . ")")
            ->distinct()
            ->get();

        // Split the slots into 'Depan' and 'Belakang'
        $slotsDepan = [];
        $slotsBelakang = [];

        foreach ($latestSlots as $slot) {
            if (in_array($slot->slot_name, array_column($slotOrder['Depan'], 'slot_name'))) {
                $slotsDepan[] = $slot;
            } elseif (in_array($slot->slot_name, array_column($slotOrder['Belakang'], 'slot_name'))) {
                $slotsBelakang[] = $slot;
            }
        }

        return response()->json([
            'Depan' => $slotsDepan,
            'Belakang' => $slotsBelakang
        ]);
    }

    public function getSlotOnPart($id_part)
    {
        $part = Part::find($id_part);
        if (!$part) {
            return response()->json(['status' => 'error', 'pesan' => 'Part tidak ditemukan'], 404);
        }

        $latestSlots = DB::table('slot__parkirs as sp1')
            ->select('sp1.id', 'sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.id_part', 'sp1.created_at')
            ->where('sp1.id_part', '=', $id_part)
            ->orderBy('sp1.id') 
            ->get();

        return response()->json([
            'part' => $part->nama,
            'slots' => $latestSlots
        ]);
    }


    public function getSlotsDibookingSelesaiDibatalkan()
    {
        // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
        $latestSlots = DB::table('slot__parkirs as sp1')
            ->select(
                'sp1.id',
                'sp1.slot_name',
                'sp1.status',
                'sp1.x',
                'sp1.y',
                'sp1.id_blok',
                'sp1.created_at',
                'parkir.id as id_parkir',
                'parkir.id_user',
                'parkir.plat_nomor',
                'parkir.waktu_booking',
                'parkir.waktu_booking_berakhir',
                'parkir_khusus.id as id_parkir_khusus',
                'parkir_khusus.id_admin',
                'parkir_khusus.plat_nomor as plat_nomor_khusus',
                'parkir_khusus.waktu_booking as waktu_booking_khusus',
                'parkir_khusus.waktu_booking_berakhir as waktu_booking_berakhir_khusus',
                'logKendaraan.exit_time',
                'logKendaraanKhusus.exit_time as exit_time_khusus'
            )
            // ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
            //                 FROM slot__parkirs
            //                 GROUP BY slot_name, id_blok) as sp2'), function($join) {
            //     $join->on('sp1.slot_name', '=', 'sp2.slot_name')
            //         ->on('sp1.id_blok', '=', 'sp2.id_blok')
            //         ->on('sp1.created_at', '=', 'sp2.max_created_at');
            // })
            ->leftJoin('parkirs as parkir', 'sp1.id', '=', 'parkir.id_slot')
            ->leftJoin('parkir_khususes as parkir_khusus', 'sp1.id', '=', 'parkir_khusus.id_slot')
            ->leftJoin('log_kendaraans as logKendaraan', 'parkir.plat_nomor', '=', 'logKendaraan.plat_nomor')
            ->leftJoin('log_kendaraans as logKendaraanKhusus', 'parkir_khusus.plat_nomor', '=', 'logKendaraanKhusus.plat_nomor')
            // ->where('parkir.id_user', '=', $userId)
            // ->where('sp1.id_blok', '=', $id_blok)
            // join untuk kendaraan khusus
            // ->leftJoin('log_kendaraans as logKendaraanKhusus', function($join) {
            //     $join->on('parkir_khusus.plat_nomor', '=', 'logKendaraanKhusus.plat_nomor')
            //         ->whereNotNull('logKendaraanKhusus.exit_time');
            // })
            // ->where(function($query) {
            //     // mengambil status slot parkir hanya 'Dibooking' yang masih aktif
            //     $query->where(function($subQuery) {
            //             $subQuery->where('sp1.status', '=', 'Dibooking')
            //                         ->where(function($subSubQuery) {
            //                             $subSubQuery->where('parkir.waktu_booking_berakhir', '>', Carbon::now())
            //                                         ->orWhere('parkir_khusus.waktu_booking_berakhir', '>', Carbon::now());
            //                         });
            //         })
            //         ->orWhere(function($subQuery) {
            //             $subQuery->where('sp1.status', '=', 'Kosong')
            //                     ->where('parkir.waktu_booking_berakhir', '1970-01-01 00:00:00'); // canceled bookings
            //         })
            //         ->orWhere(function($subQuery) {
            //             $subQuery->where('sp1.status', '=', 'Kosong')
            //                 ->whereNotNull('parkir.waktu_booking')
            //                 ->where(DB::raw('TIMESTAMPDIFF(MINUTE, parkir.waktu_booking, NOW())'), '>=', 60); // unoccupied after 1 hour
            //         })
            //         ->orWhere(function($subQuery) {
            //             $subQuery->where('sp1.status', '=', 'Kosong')
            //                 ->whereNotNull('parkir_khusus.waktu_booking')
            //                 ->where(DB::raw('TIMESTAMPDIFF(MINUTE, parkir_khusus.waktu_booking, NOW())'), '>=', 60); // unoccupied after 1 hour
            //         })
            //         ->orWhere(function($subQuery) {
            //             $subQuery->where('sp1.status', '=', 'Kosong')
            //                 ->whereNotNull('logKendaraan.exit_time'); // marked as finished with exit_time
            //         })
            //         ->orWhere(function($subQuery) {
            //             $subQuery->where('sp1.status', '=', 'Kosong')
            //                 ->whereNotNull('logKendaraanKhusus.exit_time'); // marked as finished with exit_time
            //         })
            //         ->orWhere(function ($subQuery) {
            //             // Kondisi untuk status Terisi
            //             $subQuery->where('sp1.status', '=', 'Terisi')
            //                      ->whereNull('logKendaraan.exit_time') // Tidak ada exit_time
            //                      ->orWhereNull('logKendaraanKhusus.exit_time') // Tidak ada exit_time khusus
            //                      ->where(function ($subSubQuery) {
            //                          // Kondisi jika booking masih dalam waktu aktif
            //                          $subSubQuery->where('parkir.waktu_booking_berakhir', '>', Carbon::now())
            //                                      ->orWhere('parkir_khusus.waktu_booking_berakhir', '>', Carbon::now());
            //                      });
            //         });
                    // ->orWhere(function($subQuery) {
                    //     $subQuery->where('sp1.status', '=', 'Terisi')
                    //         ->whereNull('logKendaraan.exit_time')
                    //         ->orWhereNull('logKendaraanKhusus.exit_time');
                    // });
                    // ->orWhere(function($subQuery) {
                    //     // Kondisi untuk status Terisi (masih dalam waktu booking)
                    //     $subQuery->where('sp1.status', '=', 'Terisi')
                    //              ->where(function($subSubQuery) {
                    //                  $subSubQuery->where('parkir.waktu_booking_berakhir', '>', Carbon::now())
                    //                              ->orWhere('parkir_khusus.waktu_booking_berakhir', '>', Carbon::now());
                    //              });
                    // });
            // })
            ->distinct()
            ->get();

        // Memfilter dan memproses slot sesuai status
        $filteredSlots = $latestSlots->map(function($slot) {
            $status = $slot->status;

            $now = Carbon::now();

            $is_khusus = !is_null($slot->id_parkir_khusus);

            $waktuBookingBerakhir = $is_khusus ? $slot->waktu_booking_berakhir_khusus : $slot->waktu_booking_berakhir;
            $exitTime = $is_khusus ? $slot->exit_time_khusus : $slot->exit_time;

            // Kondisi Pembatalan
            if ($slot->status == 'Kosong' && $waktuBookingBerakhir == '1970-01-01 00:00:00') {
                $status = 'Dibatalkan';
            }

            // Menandai booking yang selesai karena tidak menempati slot
            elseif (($slot->status == 'Dibooking' || $slot->status == 'Kosong') && 
                    !is_null($waktuBookingBerakhir) && 
                    $now->greaterThan($waktuBookingBerakhir) &&
                    is_null($exitTime)) {
                $status = 'Selesai (Tidak Menempati)';
            }

            // Menandai booking yang selesai berdasarkan exit_time
            elseif (!is_null($exitTime)) {
                $status = 'Selesai (Exit Time)';
            }

            // Menandai slot yang sedang terisi (masih dalam waktu booking)
            elseif ($slot->status == 'Terisi' && 
                    !is_null($waktuBookingBerakhir) && 
                    $now->lessThanOrEqualTo($waktuBookingBerakhir)) {
                $status = 'Terisi';
            }

            // if ($slot->status == 'Terisi' && is_null($slot->exit_time) || is_null($slot->exit_time_khusus)) {
            //     $status = 'Terisi';
            // }

            // Hanya mengembalikan data jika status sesuai dengan yang diminta
            if (in_array($status, ['Dibatalkan', 'Selesai (Tidak Menempati)', 'Selesai (Exit Time)', 'Terisi'])) {
                return [
                    'id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'status' => $status,
                    'x' => $slot->x,
                    'y' => $slot->y,
                    'id_blok' => $slot->id_blok,
                    'created_at' => $slot->created_at,
                    'id_parkir' => $slot->id_parkir,
                    'id_parkir_khusus' => $slot->id_parkir_khusus,
                    'id_user' => $slot->id_user,
                    'id_admin' => $slot->id_admin,
                    'plat_nomor' => $slot->plat_nomor,
                    'plat_nomor_khusus' => $slot->plat_nomor_khusus,
                    'waktu_booking' => $slot->waktu_booking,
                    'waktu_booking_berakhir' => $slot->waktu_booking_berakhir,
                    'waktu_booking_khusus' => $slot->waktu_booking_khusus,
                    'waktu_booking_berakhir_khusus' => $slot->waktu_booking_berakhir_khusus,
                    'exit_time' => $slot->exit_time,
                    'exit_time_khusus' => $slot->exit_time_khusus
                ];
            }

            return null;
        })->filter()->values();

        return response()->json($filteredSlots);
    }

    public function getSlotsDibookingTerisiSelesaiDibatalkanUser($id_blok, $userId)
    {
        // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
            $latestSlots = DB::table('slot__parkirs as sp1')
            ->select(
                'sp1.id',
                'sp1.slot_name',
                'sp1.status',
                'sp1.x',
                'sp1.y',
                'sp1.id_blok',
                'sp1.created_at',
                'parkir.id as id_parkir',
                'parkir.id_user',
                'parkir.plat_nomor',
                'parkir.waktu_booking',
                'parkir.waktu_booking_berakhir',
                'logKendaraan.exit_time'
            )
            ->leftJoin('parkirs as parkir', 'sp1.id', '=', 'parkir.id_slot')
            ->leftJoin('log_kendaraans as logKendaraan', 'parkir.plat_nomor', '=', 'logKendaraan.plat_nomor')
            ->where('parkir.id_user', '=', $userId)
            ->where('sp1.id_blok', '=', $id_blok)
            ->distinct()
            ->get();

        // Mengecek apakah pada blok yang dituju ada pesanan tidak
        if ($latestSlots->isEmpty() ){
            return response()->json(['status' => 'error', 'pesan' => 'Tidak ada pesanan yang ditemukan pada blok ini'], Response::HTTP_NOT_FOUND);
        }

        // Memfilter dan memproses slot sesuai status
        $filteredSlots = $latestSlots->map(function($slot) {
            $status = $slot->status;

            // Kondisi Pembatalan
            if ($slot->status == 'Kosong' && $slot->waktu_booking_berakhir == '1970-01-01 00:00:00') {
                $status = 'Dibatalkan';
            }

            // Menandai booking yang selesai karena tidak menempati slot selama 1 jam
            // $bookingExpired = Carbon::parse($slot->waktu_booking)->addMinutes();
            if ($slot->status == 'Dibooking' && Carbon::now()->greaterThan($slot->waktu_booking_berakhir)) {
                $status = 'Selesai (Tidak Menempati)';
            }

            // Menandai booking yang selesai berdasarkan exit_time
            if (!is_null($slot->exit_time)) {
                $status = 'Selesai (Exit Time)';
            }

            // Menandai slot yang sedang terisi (masih dalam waktu booking)
            if ($slot->status == 'Terisi' && (Carbon::now()->lessThanOrEqualTo($slot->waktu_booking_berakhir))) {
                $status = 'Terisi';
            }

            return [
                'id' => $slot->id,
                'slot_name' => $slot->slot_name,
                'status' => $status,
                'x' => $slot->x,
                'y' => $slot->y,
                'id_blok' => $slot->id_blok,
                'created_at' => $slot->created_at,
                'id_parkir' => $slot->id_parkir,
                'id_user' => $slot->id_user,
                'plat_nomor' => $slot->plat_nomor,
                'waktu_booking' => $slot->waktu_booking,
                'waktu_booking_berakhir' => $slot->waktu_booking_berakhir,
                'exit_time' => $slot->exit_time
            ];
        });

        return response()->json($filteredSlots);
    }

    // public function getSlotsDibookingSelesaiDibatalkanKhusus()
    // {
    //     // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
    //     $latestSlots = DB::table('slot__parkirs as sp1')
    //         ->select(
    //             'sp1.id',
    //             'sp1.slot_name',
    //             'sp1.status',
    //             'sp1.x',
    //             'sp1.y',
    //             'sp1.id_blok',
    //             'sp1.created_at',
    //             // 'parkir.id as id_parkir',
    //             // 'parkir.id_user',
    //             // 'parkir.plat_nomor',
    //             // 'parkir.waktu_booking',
    //             // 'parkir.waktu_booking_berakhir',
    //             'parkir_khusus.id as id_parkir_khusus',
    //             'parkir_khusus.id_admin',
    //             'parkir_khusus.plat_nomor as plat_nomor_khusus',
    //             'parkir_khusus.waktu_booking as waktu_booking_khusus',
    //             'parkir_khusus.waktu_booking_berakhir as waktu_booking_berakhir_khusus',
    //             // 'logKendaraan.exit_time',
    //             'logKendaraanKhusus.exit_time as exit_time_khusus'
    //         )
    //         // ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
    //         //                 FROM slot__parkirs
    //         //                 GROUP BY slot_name, id_blok) as sp2'), function($join) {
    //         //     $join->on('sp1.slot_name', '=', 'sp2.slot_name')
    //         //         ->on('sp1.id_blok', '=', 'sp2.id_blok')
    //         //         ->on('sp1.created_at', '=', 'sp2.max_created_at');
    //         // })
    //         ->leftJoin('parkir_khususes as parkir_khusus', 'sp1.id', '=', 'parkir_khusus.id_slot')
    //         // ->leftJoin('parkir_khususes as parkir_khusus', 'sp1.id', '=', 'parkir_khusus.id_slot')
    //         ->leftJoin('log_kendaraans as logKendaraanKhusus', 'parkir_khusus.plat_nomor', '=', 'logKendaraanKhusus.plat_nomor')
    //         // ->where('parkir.id_user', '=', $userId)
    //         // ->where('sp1.id_blok', '=', $id_blok)
    //         // join untuk kendaraan khusus
    //         // ->leftJoin('log_kendaraans as logKendaraanKhusus', function($join) {
    //         //     $join->on('parkir_khusus.plat_nomor', '=', 'logKendaraanKhusus.plat_nomor')
    //         //         ->whereNotNull('logKendaraanKhusus.exit_time');
    //         // })
    //         // ->where(function($query) {
    //         //     // mengambil status slot parkir hanya 'Dibooking' yang masih aktif
    //         //     $query->where(function($subQuery) {
    //         //             $subQuery->where('sp1.status', '=', 'Dibooking')
    //         //                         ->where(function($subSubQuery) {
    //         //                             $subSubQuery->where('parkir.waktu_booking_berakhir', '>', Carbon::now())
    //         //                                         ->orWhere('parkir_khusus.waktu_booking_berakhir', '>', Carbon::now());
    //         //                         });
    //         //         })
    //         //         ->orWhere(function($subQuery) {
    //         //             $subQuery->where('sp1.status', '=', 'Kosong')
    //         //                     ->where('parkir.waktu_booking_berakhir', '1970-01-01 00:00:00'); // canceled bookings
    //         //         })
    //         //         ->orWhere(function($subQuery) {
    //         //             $subQuery->where('sp1.status', '=', 'Kosong')
    //         //                 ->whereNotNull('parkir.waktu_booking')
    //         //                 ->where(DB::raw('TIMESTAMPDIFF(MINUTE, parkir.waktu_booking, NOW())'), '>=', 60); // unoccupied after 1 hour
    //         //         })
    //         //         ->orWhere(function($subQuery) {
    //         //             $subQuery->where('sp1.status', '=', 'Kosong')
    //         //                 ->whereNotNull('parkir_khusus.waktu_booking')
    //         //                 ->where(DB::raw('TIMESTAMPDIFF(MINUTE, parkir_khusus.waktu_booking, NOW())'), '>=', 60); // unoccupied after 1 hour
    //         //         })
    //         //         ->orWhere(function($subQuery) {
    //         //             $subQuery->where('sp1.status', '=', 'Kosong')
    //         //                 ->whereNotNull('logKendaraan.exit_time'); // marked as finished with exit_time
    //         //         })
    //         //         ->orWhere(function($subQuery) {
    //         //             $subQuery->where('sp1.status', '=', 'Kosong')
    //         //                 ->whereNotNull('logKendaraanKhusus.exit_time'); // marked as finished with exit_time
    //         //         })
    //         //         ->orWhere(function ($subQuery) {
    //         //             // Kondisi untuk status Terisi
    //         //             $subQuery->where('sp1.status', '=', 'Terisi')
    //         //                      ->whereNull('logKendaraan.exit_time') // Tidak ada exit_time
    //         //                      ->orWhereNull('logKendaraanKhusus.exit_time') // Tidak ada exit_time khusus
    //         //                      ->where(function ($subSubQuery) {
    //         //                          // Kondisi jika booking masih dalam waktu aktif
    //         //                          $subSubQuery->where('parkir.waktu_booking_berakhir', '>', Carbon::now())
    //         //                                      ->orWhere('parkir_khusus.waktu_booking_berakhir', '>', Carbon::now());
    //         //                      });
    //         //         });
    //                 // ->orWhere(function($subQuery) {
    //                 //     $subQuery->where('sp1.status', '=', 'Terisi')
    //                 //         ->whereNull('logKendaraan.exit_time')
    //                 //         ->orWhereNull('logKendaraanKhusus.exit_time');
    //                 // });
    //                 // ->orWhere(function($subQuery) {
    //                 //     // Kondisi untuk status Terisi (masih dalam waktu booking)
    //                 //     $subQuery->where('sp1.status', '=', 'Terisi')
    //                 //              ->where(function($subSubQuery) {
    //                 //                  $subSubQuery->where('parkir.waktu_booking_berakhir', '>', Carbon::now())
    //                 //                              ->orWhere('parkir_khusus.waktu_booking_berakhir', '>', Carbon::now());
    //                 //              });
    //                 // });
    //         // })
    //         ->distinct()
    //         ->get();

    //     // Memfilter dan memproses slot sesuai status
    //     $filteredSlots = $latestSlots->map(function($slot) {
    //         $status = $slot->status;

    //         $now = Carbon::now();

    //         // Kondisi Pembatalan
    //         if ($slot->status == 'Kosong' && $slot->waktu_booking_berakhir_khusus == '1970-01-01 00:00:00') {
    //             $status = 'Dibatalkan';
    //         }

    //         // Menandai booking yang selesai karena tidak menempati slot
    //         elseif (($slot->status == 'Dibooking' || $slot->status == 'Kosong') && 
    //                 !is_null($slot->waktu_booking_berakhir_khusus) && 
    //                 $now->greaterThan($slot->waktu_booking_berakhir_khusus) &&
    //                 is_null($slot->exit_time_khusus)) {
    //             $status = 'Selesai (Tidak Menempati)';
    //         }

    //         // Menandai booking yang selesai berdasarkan exit_time
    //         elseif (!is_null($slot->exit_time_khusus)) {
    //             $status = 'Selesai (Exit Time)';
    //         }

    //         // Menandai slot yang sedang terisi (masih dalam waktu booking)
    //         elseif ($slot->status == 'Terisi' && 
    //                 !is_null($slot->waktu_booking_berakhir_khusus) && 
    //                 $now->lessThanOrEqualTo($slot->waktu_booking_berakhir_khusus)) {
    //             $status = 'Terisi';
    //         }

    //         // if ($slot->status == 'Terisi' && is_null($slot->exit_time) || is_null($slot->exit_time_khusus)) {
    //         //     $status = 'Terisi';
    //         // }

    //         // Hanya mengembalikan data jika status sesuai dengan yang diminta
    //         if (in_array($status, ['Dibatalkan', 'Selesai (Tidak Menempati)', 'Selesai (Exit Time)', 'Terisi'])) {
    //             return [
    //                 'id' => $slot->id,
    //                 'slot_name' => $slot->slot_name,
    //                 'status' => $status,
    //                 'x' => $slot->x,
    //                 'y' => $slot->y,
    //                 'id_blok' => $slot->id_blok,
    //                 'created_at' => $slot->created_at,
    //                 // 'id_parkir' => $slot->id_parkir,
    //                 'id_parkir_khusus' => $slot->id_parkir_khusus,
    //                 // 'id_user' => $slot->id_user,
    //                 'id_admin' => $slot->id_admin,
    //                 // 'plat_nomor' => $slot->plat_nomor,
    //                 'plat_nomor_khusus' => $slot->plat_nomor_khusus,
    //                 // 'waktu_booking' => $slot->waktu_booking,
    //                 // 'waktu_booking_berakhir' => $slot->waktu_booking_berakhir,
    //                 'waktu_booking_khusus' => $slot->waktu_booking_khusus,
    //                 'waktu_booking_berakhir_khusus' => $slot->waktu_booking_berakhir_khusus,
    //                 // 'exit_time' => $slot->exit_time,
    //                 'exit_time_khusus' => $slot->exit_time_khusus
    //             ];
    //         }

    //         return null;
    //     })->filter()->values();

    //     return response()->json($filteredSlots);
    // }

    public function getSlotTerisi() {
        $latestSlots = DB::table('slot__parkirs as sp1')
            ->select('sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.created_at')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at 
                            FROM slot__parkirs 
                            GROUP BY slot_name, id_blok) as sp2'), function($join){
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                ->on('sp1.id_blok', '=', 'sp2.id_blok')
                ->on('sp1.created_at', '=', 'sp2.max_created_at');
        })
        ->whereIn('sp1.status', ['Dibooking', 'Terisi'])
        ->distinct()
        ->get();

        // Menghitung slot yang tersedia dan total slot
        $slotsTerisiCount = $latestSlots->whereIn('status', ['Dibooking', 'Terisi'])->count();

        return response()->json([
            'slot_terisi' => $slotsTerisiCount,
            'data' => $latestSlots,
        ]);
    }

    public function getSlotSelesai() {
        $latestSlots = DB::table('slot__parkirs as sp1')
            ->select('sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.created_at')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at 
                            FROM slot__parkirs 
                            GROUP BY slot_name, id_blok) as sp2'), function($join){
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                ->on('sp1.id_blok', '=', 'sp2.id_blok')
                ->on('sp1.created_at', '=', 'sp2.max_created_at');
        })
        ->where('sp1.status', '=', 'Kosong')
        ->distinct()
        ->get();

        // Menghitung slot yang tersedia dan total slot
        $slotSelesai = $latestSlots->where('status', 'Kosong')->count();
        // $totalSlots = $latestSlots->count();
        // $slotAktif = $slotTerisi . '/' . $totalSlots;

        return response()->json([
            // 'total_slot' => $totalSlots,
            'slot_selesai' => $slotSelesai
        ]);
    }

    public function updateAndGetExpiredBookings() {
        try {
            // Ambil semua slot parkir yang berstatus 'Dibooking' dan join dengan data booking yang masih berlaku
            $slotsToUpdate = DB::table('slot__parkirs')
            ->leftJoin('parkirs', function($join) {
                $join->on('slot__parkirs.id', '=', 'parkirs.id_slot')
                    ->where('parkirs.waktu_booking_berakhir', '>', Carbon::now());
            })
            ->leftJoin('parkir_khususes', function($join) {
                $join->on('slot__parkirs.id', '=', 'parkir_khususes.id_slot')
                    ->where('parkir_khususes.waktu_booking_berakhir', '>', Carbon::now());
            })
            ->where('slot__parkirs.status', 'Dibooking')  // Filter hanya yang berstatus 'Dibooking'
            ->whereNull('parkirs.id')                    // Hanya slot yang tidak memiliki booking aktif
            ->whereNull('parkir_khususes.id')
            ->select('slot__parkirs.id')
            ->get();

            Log::info('Ditemukan ' . count($slotsToUpdate) . ' slot parkir yang bookingnya sudah habis dan belum ditempati.');

        // Update status 'Dibooking' menjadi 'Kosong' untuk slot yang tidak ada booking aktif
        foreach ($slotsToUpdate as $slot) {
            Slot_Parkir::where('id', $slot->id)->update(['status' => 'Kosong']);
            Log::info('Status slot parkir dengan ID ' . $slot->id . ' diubah menjadi Kosong.');
        }

        Log::info('Proses update slot parkir yang expired selesai.');
    
            return response()->json([
                'status' => 'success',
                'pesan' => 'Slot parkir yang tidak ditempati dan bookingnya telah berakhir telah diubah menjadi Kosong.',
            ], Response::HTTP_OK);
    
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengupdate slot parkir: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'pesan' => 'Terjadi kesalahan saat memproses pesanan parkir yang telah berakhir',
                'data' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function create(Request $request, $id_blok)
    {
        $blok = Blok::find($id_blok);
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }
        $this->validate($request, [
            'id_part' => 'required|string|exists:parts,id',
            'slot_name' => 'required|string',
            'status' => 'required|in:Kosong,Dibooking,Terisi',
            'x' => 'required|string',
            'y' => 'required|string'
        ]);

        $slot_parkir = $request->only(['id_part', 'slot_name', 'status', 'x', 'y', 'id_blok']);
        $slot_parkir ['id_blok'] = $blok->id;

        try {
            $data = Slot_Parkir::create($slot_parkir);
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

    public function createForAll(Request $request, $id_part, $id_blok)
    {
        \Log::info('SlotParkirController createForAll called', [
            'request_data' => $request->all(),
            'id_part' => $id_part,
            'id_blok' => $id_blok
        ]);

        $this->validate($request, [
            'slots' => 'sometimes|array', 
            'slots.*.slot_name' => 'sometimes|integer',
            'slots.*.x' => 'required|string',
            'slots.*.y' => 'required|string',
            'slots.*.status' => 'sometimes|string|in:Kosong,Dibooking,Terisi',
        ]);

        $slots = $request->get('slots', []);
        $createdSlots = [];

        // If no slots to create, return success
        if (empty($slots)) {
            return response()->json([
                'status' => 'success',
                'pesan' => 'Tidak ada data slot untuk ditambahkan',
                'data' => []
            ], Response::HTTP_OK);
        }

        try {
            foreach ($slots as $index => $slot) {
                $slotData = [
                    'slot_name' => $slot['slot_name'] ?? ($index + 1),
                    'x' => $slot['x'],
                    'y' => $slot['y'],
                    'status' => $slot['status'] ?? 'Kosong',
                    'id_blok' => $id_blok,
                    'id_part' => $id_part
                ];
                $createdSlots[] = Slot_Parkir::create($slotData);
            }

            return response()->json([
                'status' => 'success',
                'pesan' => 'Semua data berhasil ditambahkan',
                'data' => $createdSlots
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    // Ubah status slot dari Kosong mnejadi Terisi berdasarkan id slot_parkir
    // public function ubahSlotKeTerisi(Request $request, $id)
    // {
    //     try {
    //         $slotParkir = Slot_Parkir::findOrFail($id);
    //         if ($slotParkir->status != 'Kosong') {
    //             return response()->json(['status' => 'error', 'pesan' => 'Slot parkir sudah dibooking atau sudah terisi'], Response::HTTP_BAD_REQUEST);
    //         }

    //         // Ubah status slot parkir menjadi "Terisi"
    //         $slotParkir->status = 'Terisi';
    //         $slotParkir->save();

    //         return response()->json(['status' => 'success', 'pesan' => 'Slot parkir berhasil diubah ke Terisi', 'data' => $slotParkir], Response::HTTP_OK);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json(['status' => 'error', 'pesan' => 'Data parkir atau slot parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan saat mengubah status slot parkir', 'data' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    // Ubah status slot dari Kosong menjadi Terisi berdasarkan slot_name
    public function ubahSlotnameKeTerisi(Request $request)
    {
        $this->validate($request, [
            'id_blok' => 'required|integer|exists:bloks,id',
            'slot_name' => 'required|integer'
        ]);

        try {
            $slotParkir = Slot_Parkir::where('slot_name', $request->slot_name)
                ->where('id_blok', $request->id_blok)
                -> orderBy('created_at', 'desc')
                ->firstOrFail();
                
            if ($slotParkir->status != 'Kosong') {
                return response()->json(['status' => 'error', 'pesan' => 'Mohon maaf slot parkir sudah dibooking, silahkan pindah ke slot parkir lain'], Response::HTTP_BAD_REQUEST);
            }

            // Ubah status slot parkir menjadi "Terisi"
            $slotParkir->status = 'Terisi';
            $slotParkir->save();

            return response()->json(['status' => 'success', 'pesan' => 'Slot parkir berhasil diubah ke Terisi', 'data' => $slotParkir], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Data parkir atau slot parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan saat mengubah status slot parkir', 'data' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ubahSlotnameKeKosong(Request $request)
    {
        $this->validate($request, [
            'id_blok' => 'required|integer|exists:bloks,id',
            'slot_name' => 'required|integer'
        ]);

        try {
            $slotParkir = Slot_Parkir::where('slot_name', $request->slot_name)
                ->where('id_blok', $request->id_blok)
                -> orderBy('created_at', 'desc')
                ->firstOrFail();
                
            if ($slotParkir->status != 'Terisi') {
                return response()->json(['status' => 'error', 'pesan' => 'Mohon maaf slot parkir sudah dibooking, silahkan pindah ke slot parkir lain'], Response::HTTP_BAD_REQUEST);
            }

            // Ubah status slot parkir menjadi "Terisi"
            $slotParkir->status = 'Kosong';
            $slotParkir->save();

            return response()->json(['status' => 'success', 'pesan' => 'Slot parkir berhasil diubah ke Kosong', 'data' => $slotParkir], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Data parkir atau slot parkir tidak ditemukan'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan saat mengubah status slot parkir', 'data' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id_blok, $id)
    {
        $blok = Blok::find($id_blok);
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $blok->slotParkirs()->with('blok')->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['status' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    public function update(Request $request, $id_blok, $id)
    {
        $blok = Blok::find($id_blok);
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $blok->slotParkirs()->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data->update($request->all());
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Diupdate', 'data' => $data], Response::HTTP_OK);
    }

    public function updateForAll(Request $request)
    {

        $this->validate($request, [
            'slots' => 'sometimes|array', 
            'slots.*.id' => 'required|exists:slot__parkirs,id', 
            'slots.*.x' => 'sometimes|string',
            'slots.*.y' => 'sometimes|string',
            'slots.*.slot_name' => 'sometimes|integer',
            'slots.*.status' => 'sometimes|string|in:Kosong,Dibooking,Terisi'
        ]);

        $slots = $request->get('slots', []);
        $updatedSlots = [];

        // If no slots to update, return success
        if (empty($slots)) {
            return response()->json([
                'status' => 'success',
                'pesan' => 'Tidak ada data slot untuk diperbarui',
                'data' => []
            ], Response::HTTP_OK);
        }

        try {
            foreach ($slots as $slot) {
                $slotData = Slot_Parkir::where('id', $slot['id'])
                    ->first();

                if ($slotData) {
                    $slotData->update($slot);
                    $updatedSlots[] = $slotData;
                }
            }

            return response()->json([
                'status' => 'success',
                'pesan' => 'Semua data berhasil diperbarui',
                'data' => $updatedSlots
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
            'ids.*' => 'required|exists:slot__parkirs,id' 
        ]);

        $ids = $request->get('ids');

        try {
            $deletedCount = Slot_Parkir::whereIn('id', $ids)
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

    public function destroy($id_blok, $id)
    {
        $blok = Blok::find($id_blok);
        if (!$blok) {
            return response()->json(['status' => 'error', 'pesan' => 'Data blok tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data = $blok->slotParkirs()->find($id);
        if (!$data) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $data->delete();
        return response()->json(['status' => 'success', 'pesan' => 'Data Berhasil Dihapus'], Response::HTTP_OK);
    }

    public function slotSelesai($id) {
        $slotData = Slot_Parkir::find($id);

        if (!$slotData) {
            return response()->json(['status' => 'error', 'pesan' => 'Data Tidak Ditemukan'], Response::HTTP_NOT_FOUND);
        }

        if ($slotData->status != 'Terisi') {
            return response()->json(['status' => 'error', 'pesan' => 'Status slot parkir harus terisi untuk diselesaikan'], Response::HTTP_BAD_REQUEST);
        }

        $slotData->status = 'Kosong';
        $slotData->save();

        return response()->json(['status' => 'success', 'pesan' => 'Slot parkir telah selesai'], Response::HTTP_OK);
    }

    public function getStatisticsByFakultas($id_fakultas)
    {
        $this->updateAndGetExpiredBookings();
        
        // Menggunakan subquery untuk mendapatkan data terbaru berdasarkan id_blok dan slot_name
        // dengan filter berdasarkan fakultas
        $latestSlotsQuery = DB::table('slot__parkirs as sp1')
            ->select('sp1.id', 'sp1.slot_name', 'sp1.status', 'sp1.x', 'sp1.y', 'sp1.id_blok', 'sp1.created_at')
            ->join(DB::raw('(SELECT slot_name, id_blok, MAX(created_at) as max_created_at
                            FROM slot__parkirs
                            GROUP BY slot_name, id_blok) as sp2'), function($join) {
                $join->on('sp1.slot_name', '=', 'sp2.slot_name')
                    ->on('sp1.id_blok', '=', 'sp2.id_blok')
                    ->on('sp1.created_at', '=', 'sp2.max_created_at');
            })
            ->join('bloks as b', 'sp1.id_blok', '=', 'b.id')
            ->where('b.id_fakultas', '=', $id_fakultas)
            ->distinct();

        $latestSlots = $latestSlotsQuery->get();

        // Data untuk total slot dan slot kosong
        $availableSlots = $latestSlots->where('status', 'Kosong')->count();
        $totalSlots = $latestSlots->count();
        $slotKosong = $availableSlots . '/' . $totalSlots;

        // Hitung jumlah slot yang dibooking
        $slotsDibookingCount = $latestSlots->where('status', 'Dibooking')->count();

        // Hitung jumlah slot terisi
        $slotsTerisiCount = $latestSlots->where('status', 'Terisi')->count();

        // Hitung jumlah slot selesai berdasarkan log kendaraan untuk fakultas ini
        $slotSelesai = LogKendaraan::join('bloks', 'log_kendaraans.id_blok', '=', 'bloks.id')
            ->where('bloks.id_fakultas', $id_fakultas)
            ->whereNotNull('log_kendaraans.exit_time')
            ->count();

        return response()->json([
            'total_slot' => $totalSlots,
            'slot_kosong' => $slotKosong,
            'kosong' => $availableSlots,
            'slot_selesai' => $slotSelesai,
            'slots_dibooking' => $slotsDibookingCount,
            'dibooking' => $slotsDibookingCount,
            'slot_terisi' => $slotsTerisiCount,
            'terisi' => $slotsTerisiCount
        ]);
    }
}