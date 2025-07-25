<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserve;
use App\Models\Slot_Parkir;
use Carbon\Carbon;

class CheckExpiredReservations extends Command
{
    protected $signature = 'reservations:check-expired';
    protected $description = 'Check and update expired reservations';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();

        // Get all expired reservations
        $expiredReservations = Reserve::where('tanggal_keluar', '<', $now)->get();

        foreach ($expiredReservations as $reservation) {
            // Update the slot status to 'Kosong'
            $slotParkir = Slot_Parkir::find($reservation->id_parkir);
            if ($slotParkir) {
                $slotParkir->status = 'Kosong';
                $slotParkir->save();
            }

            // Delete the expired reservation
            $reservation->delete();
        }

        $this->info('Expired reservations have been processed successfully.');
    }
}