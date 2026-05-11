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

    public function orders()
    {
        return $this->hasMany(Order::class, 'remote_jid', 'remote_jid');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'remote_jid', 'remote_jid');
    }
}
