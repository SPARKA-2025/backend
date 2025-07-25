<?php

namespace App\Models;

use App\Events\SlotParkirStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Slot_Parkir extends Model
{

    protected $table = 'slot__parkirs';
    protected $fillable = ['id_blok', 'slot_name', 'status', 'x', 'y', 'id_part'];

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
        return $this->belongsTo(Blok::class, 'id_blok', 'id');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'id_part', 'id');
    }

    public function parkirs()
    {
        return $this->hasMany(Parkir::class, 'id_slot', 'id');
    }

    public function reserves()
    {
        return $this->hasMany(Reserve::class, 'id_parkir', 'id');
    }

    public function cctvdata()
    {
        return $this->hasMany(cctvData::class, 'id_parkir', 'id');
    }

    // use HasFactory;

    // protected $guarded = [];

    // public function isOccupied(): bool
    // {
    //     return $this->is_occupied;
    // }

    // public function setAsOccupied(string $slot_number): void
    // {
    //     $this->slot_number = $slot_number;
    //     $this->is_occupied = true;
    //     $this->save();
    // }

    // public function setAsVacant(): void
    // {
    //     $this->slot_number = null;
    //     $this->is_occupied = false;
    //     $this->save();
    // }
}