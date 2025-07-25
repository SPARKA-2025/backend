<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessibilityOperator extends Model
{
    protected $table = 'accessibility_operators';

    protected $fillable = [
        'id_operator',
        'id_fakultas',
    ];
    
    public function operator()
    {
        return $this->hasMany(Operator::class, 'id_operator', 'id');
    }

    public function fakultas()
    {
        return $this->hasMany(Fakultas::class, 'id_fakultas', 'id');
    }
}
