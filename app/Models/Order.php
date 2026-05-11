<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'remote_jid',
        'customer_name',
        'phone',
        'delivery_address',
        'items_text',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'remote_jid', 'remote_jid');
    }
}

