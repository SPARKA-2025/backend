<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LogKendaraan extends Model
{
    //
    protected $table = 'log_kendaraans';
    protected $fillable = ['id_fakultas', 'id_blok', 'plat_nomor', 'capture_time', 'exit_time', 'vehicle', 'image'];

    protected $dates = ['capture_time', 'exit_time', 'created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function parkir()
    {
        return $this->belongsTo(Parkir::class, 'id_parkir', 'id');
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas', 'id');
    }
    public function blok()
    {
        return $this->belongsTo(Blok::class, 'id_blok', 'id');
    }
}