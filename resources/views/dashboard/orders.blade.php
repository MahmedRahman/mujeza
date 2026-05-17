@extends('layouts.app')

@section('title', 'الطلبات')
@section('page_title', 'الطلبات')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">الطلبات</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            من هنا تتابع كل الطلبات وتطلع على الفاتورة.
        </p>

        <div style="display:flex; gap:12px; align-items:center; margin-bottom:20px; flex-wrap:wrap;">
            <a href="{{ route('orders.create') }}" style="display:inline-block; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
                + إنشاء طلب جديد
            </a>
        </div>

        <form method="GET" action="{{ route('orders.index') }}" style="margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label for="customer_name" style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">اسم العميل</label>
                <input
                    id="customer_name"
                    name="customer_name"
                    type="text"
                    value="{{ $customerName ?? '' }}"
                    placeholder="مثال: محمد أحمد"
                    style="width:220px; max-width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;"
                >
            </div>
            <div>
                <label for="phone" style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">رقم التليفون</label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    value="{{ $phone ?? '' }}"
                    placeholder="مثال: 96550000000"
                    style="width:200px; max-width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;"
                >
            </div>
            <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:800; font-family:inherit; cursor:pointer;">
                بحث
            </button>
            @if ($hasFilters ?? false)
                <a href="{{ route('orders.index') }}" style="display:inline-block; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 14px; border-radius:8px; font-weight:800;">
                    إلغاء البحث
                </a>
            @endif
        </form>

        @if ($orders->isEmpty())
            <p style="margin:0; color:#6b7280;">
                @if ($hasFilters ?? false)
                    لا توجد طلبات مطابقة لبحثك.
                @else
                    لا توجد طلبات بعد.
                @endif
            </p>
        @else
            <div style="overflow-x:auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">رقم الطلب</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">الاسم</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">التليفون</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">العنوان</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">المنتجات</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">الحالة</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">تاريخ الإدخال</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700;">
                                    {{ $order->order_number }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:600; white-space:nowrap;">
                                    @php $displayName = $order->displayCustomerName(); @endphp
                                    @if ($order->customer && $displayName !== '—')
                                        <a href="{{ route('customers.edit', $order->customer->remote_jid) }}"
                                           style="text-decoration:none; color:#92400e;">
                                            {{ $displayName }}
                                        </a>
                                    @elseif ($displayName !== '—')
                                        <span style="color:#374151;">{{ $displayName }}</span>
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:600; white-space:nowrap; direction:ltr; text-align:right;">
                                    @php $displayPhone = $order->displayPhone(); @endphp
                                    @if ($displayPhone !== '—')
                                        <span style="font-family:monospace; color:#374151;">{{ $displayPhone }}</span>
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#4b5563; font-size:13px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    @if ($order->customer?->address)
                                        {{ $order->customer->address }}
                                    @elseif ($order->delivery_address)
                                        {{ $order->delivery_address }}
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#374151; font-size:13px; max-width:240px;">
                                    @if ($order->items->isNotEmpty())
                                        <div style="display:grid; gap:4px;">
                                            @foreach ($order->items as $item)
                                                <div style="font-weight:700; line-height:1.4;">
                                                    <span style="color:#92400e; font-weight:900;">#{{ $item->product_id }}</span>
                                                    {{ $item->product_title }}
                                                    <span style="color:#6b7280; font-weight:600;">×{{ $item->quantity }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif (!empty($order->items_text))
                                        <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">{{ $order->items_text }}</span>
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    @php
                                        $statusStyle = match ($order->status) {
                                            'طلب جديد' => 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;',
                                            'تم التأكيد' => 'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;',
                                            'قيد التجهيز' => 'background:#fffbeb; color:#92400e; border:1px solid #fcd34d;',
                                            'خرج للتوصيل' => 'background:#faf5ff; color:#7e22ce; border:1px solid #e9d5ff;',
                                            'مكتمل' => 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;',
                                            'ملغي' => 'background:#fff1f2; color:#b91c1c; border:1px solid #fecaca;',
                                            default => 'background:#f3f4f6; color:#374151; border:1px solid #e5e7eb;',
                                        };
                                    @endphp
                                    <span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:800; {{ $statusStyle }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700; color:#374151;">
                                    {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                                    <div style="font-size:12px; font-weight:600; color:#6b7280;">
                                        {{ $order->created_at?->diffForHumans() ?? '' }}
                                    </div>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('orders.show', $order) }}" style="text-decoration:none; border:1px solid #d4af37; background:#fffbeb; color:#92400e; padding:7px 10px; border-radius:8px; font-weight:700;">
                                            تفاصيل
                                        </a>
                                        <a href="{{ route('orders.edit', $order) }}" style="text-decoration:none; border:1px solid #fcd34d; background:#fff; color:#92400e; padding:7px 10px; border-radius:8px; font-weight:700;">
                                            تعديل
                                        </a>
                                        <a href="{{ route('orders.invoice', $order) }}" style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:7px 10px; border-radius:8px; font-weight:700;">
                                            فاتورة
                                        </a>
                                        <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('هل أنت متأكد من حذف الطلب؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:7px 10px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

