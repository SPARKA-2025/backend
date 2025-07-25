<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Parkir;
use App\Models\Slot_Parkir;

class CheckExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update expired parking bookings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();

        // Ambil semua booking yang sudah kadaluarsa
        $expiredBookings = Parkir::where('waktu_booking', '<=', $now->subHour())
                                  ->whereHas('slot__parkir', function ($query) {
                                      $query->where('status', 'Dibooking');
                                  })
                                  ->get();

        foreach ($expiredBookings as $booking) {
            // Ubah status slot parkir menjadi Kosong
            $slotParkir = Slot_Parkir::findOrFail($booking->id_slot);
            $slotParkir->status = 'Kosong';
            $slotParkir->save();

            $this->info('Slot parkir ID ' . $slotParkir->id . ' telah dikosongkan.');
        }

        $this->info('Pengecekan booking kadaluarsa selesai.');
    
    }
}
