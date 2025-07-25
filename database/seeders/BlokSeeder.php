<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Blok;
use Carbon\Carbon;

class BlokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['id_fakultas' => '1', 'nama' => 'Blok 1', 'panjang' => '1', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Musholla Rektorat'],
            ['id_fakultas' => '1', 'nama' => 'Blok 2', 'panjang' => '1', 'lebar' => '2', 'panjang_area' => '1', 'lebar_area' => '', 'ukuran_box' => '1', 'deskripsi' => 'Kantin Rektorat'],
            ['id_fakultas' => '2', 'nama' => 'Blok 1', 'panjang' => '2', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '2', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '3', 'nama' => 'Blok 1', 'panjang' => '3', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '3', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '4', 'nama' => 'Blok 1', 'panjang' => '4', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '4', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '5', 'nama' => 'Blok 1', 'panjang' => '5', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '5', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '6', 'nama' => 'Blok 1', 'panjang' => '6', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '6', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '7', 'nama' => 'Blok 1', 'panjang' => '7', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '7', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '8', 'nama' => 'Blok 1', 'panjang' => '8', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '8', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '9', 'nama' => 'Blok 1', 'panjang' => '9', 'lebar' => '1', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok'],
            ['id_fakultas' => '9', 'nama' => 'Blok 2', 'panjang' => '110.396866941956', 'lebar' => '-7.048236137420003', 'panjang_area' => '1', 'lebar_area' => '1', 'ukuran_box' => '1', 'deskripsi' => 'Blok']
        ];

        foreach ($data as $value) {
            Blok::insert([
                'id_fakultas' => $value['id_fakultas'],
                'nama' => $value['nama'],
                'panjang' => $value['panjang'],
                'lebar' => $value['lebar'],
                'panjang_area' => $value['panjang_area'],
                'lebar_area' => $value['lebar_area'],
                'ukuran_box' => $value['ukuran_box'],
                'deskripsi' => $value['deskripsi'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
