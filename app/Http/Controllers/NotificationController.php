<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parkir;
use App\Models\ParkirKhusus;
use App\Models\Slot_Parkir;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get public parking status for notifications (last 7 days)
     * No authentication required, returns limited safe data
     */
    public function getPublicParkingStatus()
    {
        try {
            // Use raw SQL to avoid Eloquent issues
            $pdo = \DB::connection()->getPdo();
            
            // Query untuk booking biasa
            $sqlRegular = "SELECT p.id, p.plat_nomor, p.created_at, s.slot_name, 'booking' as activity_type, 'biasa' as booking_type
                          FROM parkirs p 
                          LEFT JOIN slot__parkirs s ON p.id_slot = s.id 
                          WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            
            // Query untuk booking khusus
            $sqlSpecial = "SELECT pk.id, pk.plat_nomor, pk.created_at, s.slot_name, 'booking' as activity_type, 'khusus' as booking_type
                          FROM parkir_khususes pk 
                          LEFT JOIN slot__parkirs s ON pk.id_slot = s.id 
                          WHERE pk.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            
            // Query untuk log kendaraan masuk (parkir terisi)
            $sqlLogEntry = "SELECT lk.id, lk.plat_nomor, lk.created_at, CONCAT('Blok-', b.nama) as slot_name, 'entry' as activity_type, 'terisi' as booking_type
                           FROM log_kendaraans lk 
                           LEFT JOIN bloks b ON lk.id_blok = b.id 
                           WHERE lk.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            
            // Query untuk log kendaraan keluar - sementara dikosongkan karena tidak ada kolom exit_time
            $sqlLogExit = "SELECT 0 as id, '' as plat_nomor, NOW() as created_at, '' as slot_name, 'exit' as activity_type, 'keluar' as booking_type
                          WHERE 1=0"; // Query kosong sementara
            
            // Gabungkan semua query dengan UNION dan urutkan berdasarkan created_at
            $sql = "($sqlRegular) UNION ($sqlSpecial) UNION ($sqlLogEntry) UNION ($sqlLogExit) ORDER BY created_at DESC LIMIT 15";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $activities = [];
            foreach ($results as $row) {
                $activityType = $row['activity_type'];
                $description = '';
                
                switch ($activityType) {
                    case 'booking':
                        $description = 'Booking slot ' . ($row['slot_name'] ?? 'Unknown');
                        break;
                    case 'entry':
                        $description = 'Masuk ke slot ' . ($row['slot_name'] ?? 'Unknown');
                        break;
                    case 'exit':
                        $description = 'Keluar dari slot ' . ($row['slot_name'] ?? 'Unknown');
                        break;
                }
                
                // Convert time to Jakarta timezone
                $jakartaTime = Carbon::parse($row['created_at'])->setTimezone('Asia/Jakarta');
                
                $activities[] = [
                    'type' => $activityType,
                    'license_plate' => substr($row['plat_nomor'], 0, 3) . '***',
                    'slot' => $row['slot_name'] ?? 'Unknown',
                    'block' => 'A', // Default block for now
                    'time' => $jakartaTime->format('Y-m-d H:i:s'),
                    'booking_type' => $row['booking_type'],
                    'description' => $description
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching parking status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public slot availability statistics
     * No authentication required, returns general statistics only
     */
    public function getPublicSlotAvailability()
    {
        try {
            $totalSlots = Slot_Parkir::count();
            $occupiedSlots = Slot_Parkir::where('status', 'Terisi')->count();
            $bookedSlots = Slot_Parkir::where('status', 'Dibooking')->count();
            $availableSlots = $totalSlots - $occupiedSlots - $bookedSlots;

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalSlots,
                    'available' => $availableSlots,
                    'occupied' => $occupiedSlots,
                    'booked' => $bookedSlots,
                    'occupancy_rate' => $totalSlots > 0 ? round((($occupiedSlots + $bookedSlots) / $totalSlots) * 100, 1) : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch slot availability',
                'data' => []
            ], 500);
        }
    }

    /**
     * Determine activity type based on parking record
     */
    private function getActivityType($parkir)
    {
        if ($parkir->waktu_keluar) {
            return 'exit';
        } elseif ($parkir->waktu_masuk) {
            return 'entry';
        } else {
            return 'booking';
        }
    }

    /**
     * Mask license plate for privacy (show only first 2 and last 2 characters)
     */
    private function maskLicensePlate($licensePlate)
    {
        if (strlen($licensePlate) <= 4) {
            return str_repeat('*', strlen($licensePlate));
        }
        
        return substr($licensePlate, 0, 2) . str_repeat('*', strlen($licensePlate) - 4) . substr($licensePlate, -2);
    }
}