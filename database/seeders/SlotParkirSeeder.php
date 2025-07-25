<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Slot_Parkir;

class SlotParkirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_blok' => '1', 'slot_name' => '1', 'status' => 'Kosong', 'x' => '1', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '2', 'status' => 'Kosong', 'x' => '2', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '3', 'status' => 'Kosong', 'x' => '3', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '4', 'status' => 'Kosong', 'x' => '4', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '5', 'status' => 'Kosong', 'x' => '5', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '6', 'status' => 'Kosong', 'x' => '6', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '7', 'status' => 'Kosong', 'x' => '8', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '8', 'status' => 'Kosong', 'x' => '9', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '9', 'status' => 'Kosong', 'x' => '10', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '10', 'status' => 'Kosong', 'x' => '11', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '11', 'status' => 'Kosong', 'x' => '12', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '12', 'status' => 'Kosong', 'x' => '13', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '13', 'status' => 'Kosong', 'x' => '14', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '14', 'status' => 'Kosong', 'x' => '15', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '15', 'status' => 'Kosong', 'x' => '16', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '16', 'status' => 'Kosong', 'x' => '17', 'y' => '1', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '17', 'status' => 'Kosong', 'x' => '1', 'y' => '3', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '18', 'status' => 'Kosong', 'x' => '1', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '19', 'status' => 'Kosong', 'x' => '4', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '20', 'status' => 'Kosong', 'x' => '5', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '21', 'status' => 'Kosong', 'x' => '6', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '22', 'status' => 'Kosong', 'x' => '7', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '23', 'status' => 'Kosong', 'x' => '8', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '24', 'status' => 'Kosong', 'x' => '9', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '25', 'status' => 'Kosong', 'x' => '10', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '26', 'status' => 'Kosong', 'x' => '11', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '27', 'status' => 'Kosong', 'x' => '13', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '28', 'status' => 'Kosong', 'x' => '14', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '29', 'status' => 'Kosong', 'x' => '15', 'y' => '4', 'id_part' => '1'],
            ['id_blok' => '1', 'slot_name' => '30', 'status' => 'Kosong', 'x' => '16', 'y' => '4', 'id_part' => '1'],

            ['id_blok' => '1', 'slot_name' => '31', 'status' => 'Kosong', 'x' => '1', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '32', 'status' => 'Kosong', 'x' => '3', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '33', 'status' => 'Kosong', 'x' => '4', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '34', 'status' => 'Kosong', 'x' => '5', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '35', 'status' => 'Kosong', 'x' => '6', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '36', 'status' => 'Kosong', 'x' => '7', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '37', 'status' => 'Kosong', 'x' => '8', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '38', 'status' => 'Kosong', 'x' => '9', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '39', 'status' => 'Kosong', 'x' => '10', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '40', 'status' => 'Kosong', 'x' => '15', 'y' => '1', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '41', 'status' => 'Kosong', 'x' => '1', 'y' => '2', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '42', 'status' => 'Kosong', 'x' => '15', 'y' => '2', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '43', 'status' => 'Kosong', 'x' => '15', 'y' => '3', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '44', 'status' => 'Kosong', 'x' => '2', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '45', 'status' => 'Kosong', 'x' => '3', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '46', 'status' => 'Kosong', 'x' => '4', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '47', 'status' => 'Kosong', 'x' => '5', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '48', 'status' => 'Kosong', 'x' => '6', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '49', 'status' => 'Kosong', 'x' => '7', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '50', 'status' => 'Kosong', 'x' => '9', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '51', 'status' => 'Kosong', 'x' => '10', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '52', 'status' => 'Kosong', 'x' => '11', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '53', 'status' => 'Kosong', 'x' => '12', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '54', 'status' => 'Kosong', 'x' => '13', 'y' => '4', 'id_part' => '2'],
            ['id_blok' => '1', 'slot_name' => '55', 'status' => 'Kosong', 'x' => '15', 'y' => '4', 'id_part' => '2'],

            // ['id_blok' => '2', 'slot_name' => '1', 'status' => 'Kosong', 'x' => '1', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '2', 'status' => 'Kosong', 'x' => '2', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '3', 'status' => 'Kosong', 'x' => '3', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '4', 'status' => 'Kosong', 'x' => '4', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '1', 'status' => 'Kosong', 'x' => '1', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '2', 'status' => 'Kosong', 'x' => '2', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '3', 'status' => 'Kosong', 'x' => '3', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '4', 'status' => 'Kosong', 'x' => '4', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '5', 'status' => 'Kosong', 'x' => '5', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '6', 'status' => 'Kosong', 'x' => '6', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '7', 'status' => 'Kosong', 'x' => '8', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '8', 'status' => 'Kosong', 'x' => '9', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '9', 'status' => 'Kosong', 'x' => '10', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '10', 'status' => 'Kosong', 'x' => '11', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '11', 'status' => 'Kosong', 'x' => '12', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '12', 'status' => 'Kosong', 'x' => '13', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '13', 'status' => 'Kosong', 'x' => '14', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '14', 'status' => 'Kosong', 'x' => '1', 'y' => '4'],
            // ['id_blok' => '2', 'slot_name' => '15', 'status' => 'Kosong', 'x' => '1', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '16', 'status' => 'Kosong', 'x' => '4', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '17', 'status' => 'Kosong', 'x' => '5', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '18', 'status' => 'Kosong', 'x' => '6', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '19', 'status' => 'Kosong', 'x' => '7', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '20', 'status' => 'Kosong', 'x' => '8', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '21', 'status' => 'Kosong', 'x' => '9', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '22', 'status' => 'Kosong', 'x' => '10', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '23', 'status' => 'Kosong', 'x' => '11', 'y' => '5'], 
            // ['id_blok' => '2', 'slot_name' => '24', 'status' => 'Kosong', 'x' => '13', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '25', 'status' => 'Kosong', 'x' => '14', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '26', 'status' => 'Kosong', 'x' => '15', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '27', 'status' => 'Kosong', 'x' => '16', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '28', 'status' => 'Kosong', 'x' => '17', 'y' => '5'],

            // ['id_blok' => '2', 'slot_name' => '29', 'status' => 'Kosong', 'x' => '1', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '30', 'status' => 'Kosong', 'x' => '3', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '31', 'status' => 'Kosong', 'x' => '4', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '32', 'status' => 'Kosong', 'x' => '5', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '33', 'status' => 'Kosong', 'x' => '6', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '34', 'status' => 'Kosong', 'x' => '7', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '35', 'status' => 'Kosong', 'x' => '8', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '36', 'status' => 'Kosong', 'x' => '9', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '37', 'status' => 'Kosong', 'x' => '10', 'y' => '1'],
            // ['id_blok' => '2', 'slot_name' => '38', 'status' => 'Kosong', 'x' => '1', 'y' => '2'],
            // ['id_blok' => '2', 'slot_name' => '39', 'status' => 'Kosong', 'x' => '2', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '40', 'status' => 'Kosong', 'x' => '3', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '41', 'status' => 'Kosong', 'x' => '4', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '42', 'status' => 'Kosong', 'x' => '5', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '43', 'status' => 'Kosong', 'x' => '6', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '44', 'status' => 'Kosong', 'x' => '7', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '45', 'status' => 'Kosong', 'x' => '9', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '46', 'status' => 'Kosong', 'x' => '10', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '47', 'status' => 'Kosong', 'x' => '11', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '48', 'status' => 'Kosong', 'x' => '12', 'y' => '5'],
            // ['id_blok' => '2', 'slot_name' => '49', 'status' => 'Kosong', 'x' => '13', 'y' => '5'],
        ];

        foreach ($data as $value) {
            Slot_Parkir::insert(
                [
                    'id_blok' => $value['id_blok'],
                    'id_part' => $value['id_part'],
                    'slot_name' => $value['slot_name'],
                    'status' => $value['status'],
                    'x' => $value['x'],
                    'y' => $value['y'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );
        }
    }
}
