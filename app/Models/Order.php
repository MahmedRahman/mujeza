<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'طلب جديد',
        'تم التأكيد',
        'قيد التجهيز',
        'خرج للتوصيل',
        'مكتمل',
        'ملغي',
    ];

    public const TRACKING_STATUSES = [
        'طلب جديد',
        'تم التأكيد',
        'قيد التجهيز',
        'خرج للتوصيل',
        'مكتمل',
    ];

    public const CLOSED_STATUSES = ['ملغي', 'مكتمل'];

    public const DEFAULT_STATUS = 'طلب جديد';

    protected $fillable = [
        'order_number',
        'remote_jid',
        'customer_name',
        'phone',
        'delivery_address',
        'items_text',
        'status',
        'status_changed_at',
        'total_amount',
        'delivery_fee',
        'internal_notes',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'delivery_fee' => 'float',
        'status_changed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'remote_jid', 'remote_jid');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function displayCustomerName(): string
    {
        $name = $this->customer?->name ?? $this->customer_name;

        return $name !== null && trim($name) !== '' ? trim($name) : '—';
    }

    public function displayPhone(): string
    {
        $phone = $this->customer?->phone ?? $this->phone;

        return $phone !== null && trim($phone) !== '' ? trim($phone) : '—';
    }

    public function displayAddress(): string
    {
        $address = $this->customer?->address ?? $this->delivery_address;

        return $address !== null && trim($address) !== '' ? trim($address) : '—';
    }

    public function itemsSubtotal(): float
    {
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return (float) $this->items->sum('line_total');
        }

        return (float) $this->total_amount;
    }

    public function grandTotal(): float
    {
        return $this->itemsSubtotal() + (float) $this->delivery_fee;
    }

    public function trackingStepIndex(): int
    {
        $index = array_search($this->status, self::TRACKING_STATUSES, true);

        return $index === false ? -1 : (int) $index;
    }
}
