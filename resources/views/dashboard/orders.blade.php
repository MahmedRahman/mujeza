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
                <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">رقم الطلب</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">remoteJid</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">الاسم</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align:right;">العنوان</th>
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
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-size:13px; direction:ltr; text-align:right;">
                                    <span style="font-family:monospace; color:#4b5563;">{{ $order->remote_jid ?? '—' }}</span>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:600; white-space:nowrap;">
                                    @if ($order->customer)
                                        <a href="{{ route('customers.edit', $order->customer->remote_jid) }}"
                                           style="text-decoration:none; color:#92400e;">
                                            {{ $order->customer->name }}
                                        </a>
                                    @elseif ($order->customer_name)
                                        <span style="color:#374151;">{{ $order->customer_name }}</span>
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
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#374151;">
                                    {{ $order->status }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700; color:#374151;">
                                    {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                                    <div style="font-size:12px; font-weight:600; color:#6b7280;">
                                        {{ $order->created_at?->diffForHumans() ?? '' }}
                                    </div>
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

