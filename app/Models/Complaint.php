<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'remote_jid',
        'title',
        'description',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'remote_jid', 'remote_jid');
    }
}

