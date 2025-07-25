<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenAdmin extends Model
{
    //
    protected $table = 'token_admins';

    protected $fillable = ['api_token', 'id_admin', 'expired_at'];

    protected $dates = [
        'expired_at',
    ];

    public function tokenAdmin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id');
    }
}
