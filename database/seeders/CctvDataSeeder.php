<?php

namespace Database\Seeders;

use App\Models\cctvData;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CctvDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['jenis_kamera' => 'Camera Gateway', 'id_fakultas' => '1', 'id_blok' => '1', 'id_part' => '2', 'url' => 'http:///34.128.83.137:8080/hls/ruang_1a/index.m3u8', 'x' => '7', 'y' => '1', 'angle' => '0'],
            ['jenis_kamera' => 'Camera 1', 'id_fakultas' => '1', 'id_blok' => '1', 'id_part' => '2', 'url' => 'http:///34.128.83.137:8080/hls/ruang_1a/index.m3u8', 'x' => '8', 'y' => '5', 'angle' => '0'],
            ['jenis_kamera' => 'Camera 2', 'id_fakultas' => '1', 'id_blok' => '1', 'id_part' => '2', 'url' => 'http:///34.128.83.137:8080/hls/ruang_1a/index.m3u8', 'x' => '15', 'y' => '5', 'angle' => '0'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '15', 'y' => '5', 'angle' => '90'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '8', 'y' => '2', 'angle' => '135'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '12', 'y' => '2', 'angle' => '180'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '12', 'y' => '2', 'angle' => '225'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '3', 'y' => '6', 'angle' => '0'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '3', 'y' => '6', 'angle' => '90'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '12', 'y' => '6', 'angle' => '180'],
            // ['url' => 'http://34.101.183.181/rtsp_1.mp4?tkn=3290990134', 'x' => '12', 'y' => '6', 'angle' => '270'],
        ];

        foreach($data as $value) {
            cctvData::insert([
                'jenis_kamera' => $value['jenis_kamera'],
                'id_fakultas' => $value['id_fakultas'],
                'id_blok' => $value['id_blok'],
                'id_part' => $value['id_part'],
                'url' => $value['url'],
                'x' => $value['x'],
                'y' => $value['y'],
                'angle' => $value['angle'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
