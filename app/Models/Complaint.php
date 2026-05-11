<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    public const STATUSES = [
        'جديدة',
        'قيد المعالجة',
        'تم الحل',
        'مغلقة',
    ];

    protected $fillable = [
        'remote_jid',
        'title',
        'description',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'remote_jid', 'remote_jid');
    }
}

