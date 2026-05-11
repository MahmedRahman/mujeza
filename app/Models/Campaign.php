<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'message',
        'phones_count',
        'success_count',
        'failed_count',
        'status',
        'results',
    ];

    protected $casts = [
        'results' => 'array',
    ];
}
