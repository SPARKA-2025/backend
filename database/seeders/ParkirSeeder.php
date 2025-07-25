<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Parkir;

class ParkirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['id_slot' => '1', 'plat_nomor' => 'AD7431KM', 'nama_pemesan' => 'Azka', 'jenis_mobil' => 'mobil', 'tanggal_masuk' => '2024-06-06 08:47:21', 'tanggal_keluar' => '2024-06-06 12:00:21']
        ];

        foreach ($data as $value) {
            Parkir::insert([
                'id_slot' => $value['id_slot'],
                'plat_nomor' => $value['plat_nomor'],
                'nama_pemesan' => $value['nama_pemesan'],
                'jenis_mobil' => $value['jenis_mobil'],
                'tanggal_masuk' => $value['tanggal_masuk'],
                'tanggal_keluar' => $value['tanggal_keluar'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
