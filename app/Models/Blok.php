<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Blok extends Model
{
    //
    protected $table = 'bloks';
    protected $fillable = ['nama', 'id_fakultas', 'panjang', 'lebar', 'panjang_area', 'lebar_area', 'ukuran_box', 'deskripsi'];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas', 'id');
    }

    public function part()
    {
        return $this->hasMany(Part::class, 'id_blok', 'id');
    }

    public function slotParkirs()
    {
        return $this->hasMany(Slot_Parkir::class, 'id_blok', 'id');
    }

    public function cctvData()
    {
        return $this->hasMany(cctvData::class, 'id_blok', 'id');
    }

    public function logKendaraan()
    {
        return $this->hasMany(LogKendaraan::class, 'id_blok', 'id');
    }
}
