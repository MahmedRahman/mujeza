@extends('layouts.app')

@section('title', 'الطلبات')
@section('page_title', 'الطلبات')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">الطلبات</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            من هنا تتابع كل الطلبات وتطلع على الفاتورة.
        </p>

        <a href="{{ route('orders.create') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
            + إنشاء طلب جديد
        </a>

        @if ($orders->isEmpty())
            <p style="margin:0; color:#6b7280;">لا توجد طلبات بعد.</p>
        @else
            <div style="overflow-x:auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 920px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">رقم الطلب</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">اسم العميل</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">تليفون</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">عنوان التوصيل</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">الحالة</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">تاريخ الإدخال</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">الإجمالي</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700;">
                                    {{ $order->order_number }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    {{ $order->customer_name }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    <a href="tel:{{ $order->phone }}" style="color:#1d4ed8; text-decoration:none; font-weight:700;">
                                        {{ $order->phone }}
                                    </a>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#374151; font-weight:600;">
                                    {{ $order->delivery_address ?: '—' }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#374151;">
                                    {{ $order->status }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700; color:#374151;">
                                    {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                                    <div style="font-size:12px; font-weight:600; color:#6b7280;">
                                        {{ $order->created_at?->diffForHumans() ?? '' }}
                                    </div>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:800;">
                                    {{ number_format((float) $order->total_amount, 2) }} د.ك
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('orders.edit', $order) }}" style="text-decoration:none; border:1px solid #fcd34d; background:#fffbeb; color:#92400e; padding:7px 10px; border-radius:8px; font-weight:700;">
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

