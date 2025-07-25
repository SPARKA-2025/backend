<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class cctvData extends Model
{
    //
    protected $table = 'cctv_data';
    protected $fillable = ['jenis_kamera', 'id_fakultas', 'id_blok','url', 'hls_url', 'stream_active', 'last_stream_start', 'last_stream_stop', 'x', 'y', 'angle', 'id_part', 'offset_x', 'offset_y'];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function slot__parkir()
    {
        return $this->belongsTo(Slot_Parkir::class, 'id_slot', 'id');
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas', 'id');
    }

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'id_blok', 'id');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'id_part', 'id');
    }
}