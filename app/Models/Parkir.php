<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Parkir extends Model
{
    //
    use HasFactory;
    protected $table = 'parkirs';
    protected $fillable = ['plat_nomor', 'jenis_mobil', 'waktu_booking', 'waktu_booking_berakhir', 'id_slot', 'id_user'];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function slot_parkir()
    {
        return $this->belongsTo(Slot_Parkir::class, 'id_slot', 'id');
    }

    public function slotParkir()
    {
        return $this->belongsTo(Slot_Parkir::class, 'id_slot', 'id');
    }

    public function reserves()
    {
        return $this->hasMany(Reserve::class, 'id_parkir', 'id');
    }

    public function logKendaraans()
    {
        return $this->hasMany(LogKendaraan::class, 'id_parkir', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}