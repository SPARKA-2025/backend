<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['nama' => 'admin', 'email' => 'admin@gmail.com', 'password' => '$2y$12$b0BieTt96g7lbaQz5HY44.MzjxmmhbRtNfmNYsC12HXw9TaL5Q9Bq'],
            ['nama' => 'admin', 'email' => 'admin1@gmail.com', 'password' => '$2y$12$S.jifJ56lciMB/l8tpulz.gpI7juNcgy1k1ArYu09AwvVmmGgtnXa'],
            ['nama' => 'test', 'email' => 'test@gmail.com', 'password' => '$2y$12$bCK7y36A1Xs0jg32jr3aref9oZex0TiKANnviQzRNY2nTx3uV/sRG']
        ];

        foreach ($data as $value) {
            Admin::insert([
                'nama' => $value['nama'],
                'email' => $value['email'],
                'password' => $value['password'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
