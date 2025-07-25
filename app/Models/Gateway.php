<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $table = 'gateways';
    protected $fillable = ['gateway_name', 'x', 'y', 'direction', 'id_part', 'id_blok'];
    protected $dates = ['created_at', 'updated_at'];

    public function part()
    {
        return $this->belongsTo(Part::class, 'id_part', 'id');
    }
}
