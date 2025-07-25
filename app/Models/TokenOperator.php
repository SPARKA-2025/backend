<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenOperator extends Model
{
    protected $table = 'token_operator';
    protected $fillable = ['api_token', 'id_operator', 'expired_at'];

    protected $dates = [
        'expired_at',
    ];

    public function tokenOperator()
    {
        return $this->belongsTo(Operator::class, 'id_operator', 'id');
    }
}
