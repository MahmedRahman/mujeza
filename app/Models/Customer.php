<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $primaryKey = 'remote_jid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'remote_jid',
        'phone',
        'name',
        'address',
    ];
}
