@extends('layouts.app')

@section('title', 'فاتورة الطلب')
@section('page_title', 'فاتورة الطلب')

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; gap: 12px; flex-wrap:wrap; align-items:center; margin-bottom: 14px;">
            <div>
                <h2 style="margin:0; font-weight: 800;">فاتورة الطلب رقم {{ $order->order_number }}</h2>
                <p style="margin:6px 0 0; color:#6b7280; font-weight:600;">
                    الحالة: {{ $order->status }}
                </p>
                <p style="margin:6px 0 0; color:#6b7280; font-weight:600;">
                    تاريخ الإدخال: {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                    @if ($order->created_at)
                        <span style="font-size:12px; font-weight:600; color:#6b7280;">({{ $order->created_at->diffForHumans() }})</span>
                    @endif
                </p>
            </div>

            <div style="display:flex; gap: 10px;">
                <a href="{{ route('orders.show', $order) }}" style="text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:8px 12px; border-radius:8px; font-weight:700;">
                    تفاصيل
                </a>
                <button type="button" onclick="window.print()" style="border:none; background:#d4af37; color:#111827; padding:8px 14px; border-radius:8px; font-weight:800; cursor:pointer;">
                    طباعة
                </button>
            </div>
        </div>

        <div style="border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2; margin-bottom: 14px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                <div>
                    <div style="font-weight:800; margin-bottom:6px;">اسم العميل</div>
                    <div style="color:#374151; font-weight:700;">{{ $order->customer_name }}</div>
                </div>
                <div>
                    <div style="font-weight:800; margin-bottom:6px;">تليفون العميل</div>
                    <div style="color:#374151; font-weight:700;">
                        <a href="tel:{{ $order->phone }}" style="color:#1d4ed8; text-decoration:none;">{{ $order->phone }}</a>
                    </div>
                </div>
                <div>
                    <div style="font-weight:800; margin-bottom:6px;">الإجمالي</div>
                    <div style="color:#111827; font-weight:900; font-size: 18px;">
                        {{ number_format((float) $order->total_amount, 2) }} د.ك
                    </div>
                </div>
            </div>
        </div>

        @if ($order->items->isEmpty())
            <p style="margin:0; color:#6b7280;">لا توجد عناصر في هذا الطلب.</p>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width: 820px;">
                    <thead>
                        <tr style="background:#f8f2de;">
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">المنتج</th>
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:140px;">سعر المنتج</th>
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:120px;">الكمية</th>
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:160px;">إجمالي السطر</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td style="padding:10px; border:1px solid #efe3b7; font-weight:700;">{{ $item->product_title }}</td>
                                <td style="padding:10px; border:1px solid #efe3b7; font-weight:800;">
                                    {{ number_format((float) $item->unit_price, 2) }} د.ك
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7; font-weight:800;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7; font-weight:900;">
                                    {{ number_format((float) $item->line_total, 2) }} د.ك
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

