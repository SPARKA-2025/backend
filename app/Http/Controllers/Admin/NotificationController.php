<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parkir;
use App\Models\ParkirKhusus;
use App\Models\LogKendaraan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function getParkingStatus()
    {
        try {
            // Hitung slot yang terbooking (booking aktif)
            $bookedRegular = Parkir::whereNotNull('waktu_booking')
                ->where('waktu_booking_berakhir', '>', Carbon::now())
                ->count();
            
            $bookedSpecial = ParkirKhusus::whereNotNull('waktu_booking')
                ->where('waktu_booking_berakhir', '>', Carbon::now())
                ->count();
            
            $totalBooked = $bookedRegular + $bookedSpecial;

            // Hitung slot yang terisi (ada log kendaraan masuk tapi belum keluar)
            $occupiedCount = LogKendaraan::where('exit_time', null)
                ->where('created_at', '>=', Carbon::now()->subHours(24)) // dalam 24 jam terakhir
                ->count();

            // Hitung kendaraan yang keluar hari ini
            $exitedToday = LogKendaraan::whereNotNull('exit_time')
                ->whereDate('exit_time', Carbon::today())
                ->count();

            // Ambil aktivitas terbaru (10 terakhir)
            $recentActivities = collect();
            
            // Aktivitas booking terbaru
            $recentBookings = Parkir::with('blok')
                ->whereNotNull('waktu_booking')
                ->where('waktu_booking', '>=', Carbon::now()->subHours(2))
                ->orderBy('waktu_booking', 'desc')
                ->take(5)
                ->get()
                ->map(function ($booking) {
                    return [
                        'plat_nomor' => $booking->plat_nomor,
                        'status' => 'booked',
                        'slot_name' => $booking->blok->nama ?? 'N/A',
                        'timestamp' => $booking->waktu_booking
                    ];
                });

            $recentSpecialBookings = ParkirKhusus::with('blok')
                ->whereNotNull('waktu_booking')
                ->where('waktu_booking', '>=', Carbon::now()->subHours(2))
                ->orderBy('waktu_booking', 'desc')
                ->take(5)
                ->get()
                ->map(function ($booking) {
                    return [
                        'plat_nomor' => $booking->plat_nomor,
                        'status' => 'booked',
                        'slot_name' => $booking->blok->nama ?? 'N/A',
                        'timestamp' => $booking->waktu_booking
                    ];
                });

            // Aktivitas masuk terbaru
            $recentEntries = LogKendaraan::with('blok')
                ->whereNull('exit_time')
                ->where('created_at', '>=', Carbon::now()->subHours(2))
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($entry) {
                    return [
                        'plat_nomor' => $entry->plat_nomor,
                        'status' => 'occupied',
                        'slot_name' => $entry->blok->nama ?? 'N/A',
                        'timestamp' => $entry->created_at
                    ];
                });

            // Aktivitas keluar terbaru
            $recentExits = LogKendaraan::with('blok')
                ->whereNotNull('exit_time')
                ->where('exit_time', '>=', Carbon::now()->subHours(2))
                ->orderBy('exit_time', 'desc')
                ->take(5)
                ->get()
                ->map(function ($exit) {
                    return [
                        'plat_nomor' => $exit->plat_nomor,
                        'status' => 'exited',
                        'slot_name' => $exit->blok->nama ?? 'N/A',
                        'timestamp' => $exit->exit_time
                    ];
                });

            // Gabungkan semua aktivitas dan urutkan berdasarkan waktu
            $recentActivities = $recentBookings
                ->concat($recentSpecialBookings)
                ->concat($recentEntries)
                ->concat($recentExits)
                ->sortByDesc('timestamp')
                ->take(10)
                ->values()
                ->map(function ($activity) {
                    // Map status to type for frontend
                    $type = 'other';
                    $description = '';
                    
                    switch ($activity['status']) {
                        case 'booked':
                            $type = 'booking';
                            $description = 'Slot ' . $activity['slot_name'] . ' dibooking';
                            break;
                        case 'occupied':
                            $type = 'entry';
                            $description = 'Masuk ke slot ' . $activity['slot_name'];
                            break;
                        case 'exited':
                            $type = 'exit';
                            $description = 'Keluar dari slot ' . $activity['slot_name'];
                            break;
                    }
                    
                    return [
                        'plat_nomor' => $activity['plat_nomor'],
                        'type' => $type,
                        'description' => $description,
                        'timestamp' => $activity['timestamp']
                    ];
                });

            return response()->json([
                'status' => 'success',
                'activities' => $recentActivities,
                'counts' => [
                    'booked' => $totalBooked,
                    'occupied' => $occupiedCount,
                    'exited' => $exitedToday
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRealtimeUpdates()
    {
        try {
            // Ambil update terbaru dalam 1 menit terakhir
            $latestUpdates = LogKendaraan::with('blok')
                ->where('created_at', '>=', Carbon::now()->subMinute())
                ->orWhere('updated_at', '>=', Carbon::now()->subMinute())
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($log) {
                    return [
                        'plat_nomor' => $log->plat_nomor,
                        'status' => $log->exit_time ? 'exited' : 'occupied',
                        'slot_name' => $log->blok->nama ?? 'N/A',
                        'timestamp' => $log->exit_time ?? $log->created_at
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $latestUpdates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil update realtime: ' . $e->getMessage()
            ], 500);
        }
    }
}