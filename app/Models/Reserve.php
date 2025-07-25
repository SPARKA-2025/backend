<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reserve extends Model
{
    use HasFactory;

    protected $table = 'reserves';

    protected $fillable = [
        'id_parkir',
        'id_user',
        'tanggal_masuk',
        'tanggal_keluar',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta')->toDateTimeString();
    }

    // Define the relationships

    public function parkir()
    {
        return $this->belongsTo(Parkir::class, 'id_parkir', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    // Add validation rules

    // public static $rules = [
    //     'id_parkir' => 'required|integer|exists:parkir,id',
    //     'id_user' => 'required|integer|exists:user,id',
    //     'tanggal_masuk' => 'required|date',
    //     'tanggal_keluar' => 'required|date|after_or_equal:tanggal_masuk',
    // ];

    // // Helper methods

    // public function isReserved($reserve)
    // {
    //     if ($reserve) {
    //         return $this->id == $reserve;
    //     }

    //     return false;
    // }

    // public function isExpired()
    // {
    //     $now = now();

    //     // Compare the tanggal_keluar with the current date
    //     // Add a time comparison if needed

    //     return $this->tanggal_keluar < $now;
    // }

    // public function isValidReservation()
    // {
    //     if (!$this->isExpired()) {
    //         // Check if the parking slot is not already reserved
    //         // during the desired time range

    //         return true;
    //     }

    //     return false;
    // }
}