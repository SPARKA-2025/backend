<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    //
    protected $table = 'token';
    protected $fillable = ['api_token', 'id_user', 'expired_at'];

    protected $dates = [
        'expired_at'
    ];

    public function token()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}

    
