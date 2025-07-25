<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['nama' => 'Azka', 'email' => 'azka@gmail.com', 'password' => '$2y$12$OJMP6UgcNUogr/bCoVTEpuEqgJ0ZzLCRsv4eAo8NW2chChS.DumzO', 'alamat' => 'Bekasi', 'phone' => '086379051369', 'plat_nomor' => 'H1962DO'],
            ['nama' => 'example', 'email' => 'example@gmail.com', 'password' => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alamat' => 'Jakarta', 'phone' => '081234567890', 'plat_nomor' => 'EX123AM'],
        ];

        foreach ($data as $value) {
            User::insert([
                'nama' => $value['nama'],
                'email' => $value['email'],
                'password' => $value['password'],
                'alamat' => $value['alamat'],
                'phone' => $value['phone'],
                'plat_nomor' => $value['plat_nomor'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
