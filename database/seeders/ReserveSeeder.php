<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reserve;
use Carbon\Carbon;

class ReserveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['id_parkir' => '1', 'id_user' => '2', 'tanggal_masuk' => '2024-03-17 09:00:00', 'tanggal_keluar' => '2024-03-17 19:00:00'],
            ['id_parkir' => '2', 'id_user' => '1', 'tanggal_masuk' => '2024-03-17 09:00:00', 'tanggal_keluar' => '2024-03-17 19:00:00'],
        ];

        foreach ($data as $value) {
            Reserve::insert([
                'id_parkir' => $value['id_parkir'],
                'id_user' => $value['id_user'],
                'tanggal_masuk' => $value['tanggal_masuk'],
                'tanggal_keluar' => $value['tanggal_keluar'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
