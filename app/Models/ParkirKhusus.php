<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParkirKhusus extends Model
{
    //
    use HasFactory;
    protected $table = 'parkir_khususes';
    protected $fillable = ['plat_nomor', 'jenis_mobil', 'waktu_booking', 'waktu_booking_berakhir', 'id_slot', 'id_admin'];

    // protected $dates = ['created_at', 'updated_at'];

    // public function getCreatedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    // }

    // public function getUpdatedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    // }

    public function slot_parkir()
    {
        return $this->belongsTo(Slot_Parkir::class, 'id_slot', 'id');
    }

    public function slotParkir()
    {
        return $this->belongsTo(Slot_Parkir::class, 'id_slot', 'id');
    }

    // public function reserves()
    // {
    //     return $this->hasMany(Reserve::class, 'id_parkir', 'id');
    // }

    public function logKendaraans()
    {
        return $this->hasMany(LogKendaraan::class, 'id_parkir', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id');
    }
}
