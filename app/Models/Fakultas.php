<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fakultas extends Model
{
    //
    protected $table = 'fakultas';
    protected $fillable = ['nama', 'deskripsi', 'image'];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function blok()
    {
        return $this->hasMany(Blok::class, 'id_fakultas');
    }

    public function cctvData()
    {
        return $this->hasMany(cctvData::class, 'id_fakultas');
    }

    public function logKendaraa()
    {
        return $this->hasMany(LogKendaraan::class, 'id_fakultas');
    }

    public function accessibility()
    {
        return $this->belongsTo(AccessibilityOperator::class, 'id_fakultas', 'id');
    }
}
