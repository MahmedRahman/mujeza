<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'phone1',
        'phone2',
        'address',
        'latitude',
        'longitude',
        'map_url',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}

