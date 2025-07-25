<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{

    protected $table = 'parts';

    protected $fillable = [
        'id_blok',
        'nama',
        'column',
        'row'
    ];


    public function blok()
    {
        return $this->belongsTo(Blok::class, 'id_blok', 'id');
    }

    public function slotParkir()
    {
        return $this->hasMany(Slot_Parkir::class, 'id_part', 'id');
    }

    public function cctv()
    {
        return $this->hasMany(cctvData::class, 'id_part', 'id');
    }

    public function gateway()
    {
        return $this->hasMany(Gateway::class, 'id_part', 'id');
    }
}
