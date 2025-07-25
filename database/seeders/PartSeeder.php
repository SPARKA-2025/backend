<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Part;

class PartSeeder extends Seeder
{
    public function run()
    {
        $datas = array(
            array("id" => 1, "id_blok" => 1, "nama" => "Bagian Depan", "column" => 17, "row" => 4),
            array("id" => 2, "id_blok" => 1, "nama" => "Bagian Belakang", "column" => 17, "row" => 4),
            array("id" => 4, "id_blok" => 2, "nama" => "Lapangan", "column" => 13, "row" => 4),
            array("id" => 5, "id_blok" => 2, "nama" => "Depan Kantor", "column" => 13, "row" => 4),
        );

        foreach ($datas as $value) {
            Part::updateOrCreate(
                [
                    'id' => $value['id'],
                ],
                [
                    
                    'id_blok' => $value['id_blok'],
                    'nama' => $value['nama'],
                    'column' => $value['column'],
                    'row' => $value['row']
                ]
            );
        }
    }
}
