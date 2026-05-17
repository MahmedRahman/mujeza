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

        if ($phone !== null && trim($phone) !== '') {
            return trim($phone);
        }

        if ($this->remote_jid) {
            $fromJid = (string) preg_replace('/@[^@]+$/', '', $this->remote_jid);

            if (trim($fromJid) !== '') {
                return trim($fromJid);
            }
        }

        return '—';
    }

    public function displayAddress(): string
    {
        $customerAddress = trim((string) ($this->customer?->address ?? ''));
        $orderAddress = trim((string) ($this->delivery_address ?? ''));

        $address = $customerAddress !== '' ? $customerAddress : $orderAddress;

        return $address !== '' ? $address : '—';
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

    /**
     * تحليل items_text للعرض في قسم «ملاحظات» (نص عادي أو JSON من واتساب/API).
     *
     * @return array{
     *     format: 'text'|'json',
     *     text: string|null,
     *     items: array<int, array{product: string, quantity: int}>,
     *     meta: array<string, string>
     * }|null
     */
    public function parsedItemsNotes(): ?array
    {
        $raw = trim((string) ($this->items_text ?? ''));
        if ($raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (! is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            return [
                'format' => 'text',
                'text'   => $raw,
                'items'  => [],
                'meta'   => [],
            ];
        }

        $items = [];
        foreach ($data['items'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $product = trim((string) ($row['product'] ?? $row['product_title'] ?? $row['title'] ?? $row['name'] ?? ''));
            if ($product === '') {
                continue;
            }

            $items[] = [
                'product'  => $product,
                'quantity' => max(1, (int) ($row['quantity'] ?? 1)),
            ];
        }

        $meta = [];
        $map  = [
            'name'             => 'الاسم',
            'customer_name'    => 'الاسم',
            'phone'            => 'الهاتف',
            'address'          => 'العنوان',
            'delivery_address' => 'العنوان',
        ];

        foreach ($map as $key => $label) {
            if (! isset($data[$key]) || ! is_scalar($data[$key])) {
                continue;
            }

            $value = trim((string) $data[$key]);
            if ($value !== '' && ! isset($meta[$label])) {
                $meta[$label] = $value;
            }
        }

        return [
            'format' => 'json',
            'text'   => null,
            'items'  => $items,
            'meta'   => $meta,
        ];
    }
}
