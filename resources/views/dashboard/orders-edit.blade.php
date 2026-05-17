@extends('layouts.app')

@section('title', 'تعديل طلب')
@section('page_title', 'تعديل طلب')

@section('content')
    <section class="card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
            <div>
                <h2 style="margin-top: 0; font-weight: 700;">تعديل الطلب #{{ $order->order_number }}</h2>
                <p style="margin:0; color: #4b5563; font-weight: 500;">
                    عدل بيانات الربط والحالة. لعرض التفاصيل الكاملة والتتبع استخدم صفحة التفاصيل.
                </p>
            </div>
            <a href="{{ route('orders.show', $order) }}" style="text-decoration:none; border:none; background:#d4af37; color:#111827; padding:8px 14px; border-radius:8px; font-weight:800; white-space:nowrap;">
                عرض التفاصيل والتتبع
            </a>
        </div>

        @if ($errors->any())
            <div style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="border:1px solid #efe3b7; border-radius:12px; padding:14px; background:#fffcf2; margin-bottom:14px;">
            <h3 style="margin:0 0 12px; font-weight:800; font-size:15px;">بيانات العميل والحالة</h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                <div>
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">الاسم</div>
                    <div style="font-weight:800; color:#111827;">{{ $order->displayCustomerName() }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">رقم الهاتف</div>
                    <div style="font-weight:800;">
                        @if ($order->displayPhone() !== '—')
                            <a href="tel:{{ $order->displayPhone() }}" style="color:#1d4ed8; text-decoration:none;">{{ $order->displayPhone() }}</a>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">العنوان</div>
                    <div style="font-weight:700; color:#374151; line-height:1.6;">{{ $order->displayAddress() }}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">الحالة الحالية</div>
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
                    <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:13px; font-weight:800; {{ $statusStyle }}">
                        {{ $order->status }}
                    </span>
                </div>
                <div>
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">تاريخ تغيير الحالة</div>
                    <div style="font-weight:800; color:#111827;">
                        {{ $order->status_changed_at?->format('d/m/Y h:i A') ?? $order->updated_at?->format('d/m/Y h:i A') ?? '—' }}
                    </div>
                    @if ($order->status_changed_at)
                        <div style="font-size:12px; color:#6b7280; font-weight:600; margin-top:2px;">
                            {{ $order->status_changed_at->diffForHumans() }}
                        </div>
                    @endif
                </div>
                <div>
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">تاريخ إنشاء الطلب</div>
                    <div style="font-weight:800; color:#111827;">
                        {{ $order->created_at?->format('d/m/Y h:i A') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        @include('dashboard.partials.order-status-panel', ['order' => $order])

        <form method="POST" action="{{ route('orders.update', $order) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="remote_jid" value="{{ old('remote_jid', $order->remote_jid) }}">

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">حالة الطلب</label>
                    <select name="status" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                        <option value="">-- اختر --</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" {{ old('status', $order->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- عرض المنتجات المسجلة في قاعدة البيانات --}}
            @if ($order->items_text)
                <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2; margin-bottom: 14px;">
                    <div style="font-weight:800; margin-bottom:8px; color:#374151;">المنتجات المطلوبة</div>
                    <div style="color:#374151; font-size:15px; line-height:1.8; white-space: pre-wrap; word-break: break-word;">{{ $order->items_text }}</div>
                </div>
            @endif

            {{-- إشعار واتساب --}}
            <div style="border:1px solid #d1fae5; border-radius:10px; padding:14px 16px; background:#f0fdf4; margin-bottom:14px; display:flex; align-items:flex-start; gap:12px;">
                <input type="checkbox" name="notify_customer" value="1" id="notify_customer"
                       style="margin-top:3px; width:18px; height:18px; accent-color:#16a34a; flex-shrink:0; cursor:pointer;">
                <label for="notify_customer" style="cursor:pointer; line-height:1.5;">
                    <span style="font-weight:800; color:#15803d; font-size:14px;">📲 إرسال إشعار واتساب للعميل</span>
                    <div style="font-size:12px; color:#4b5563; margin-top:3px;">
                        @if ($order->remote_jid)
                            سيتم إرسال رسالة واتساب للعميل بحالة الطلب الجديدة
                        @else
                            <span style="color:#ef4444;">لا يمكن الإرسال — لا يوجد رقم واتساب مرتبط بهذا الطلب</span>
                        @endif
                    </div>
                </label>
            </div>

            <div style="margin-top: 14px;">
                <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                    حفظ التعديلات
                </button>
                <a href="{{ route('orders.index') }}" style="display:inline-block; margin-inline-start: 10px; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 14px; border-radius:8px; font-weight:700;">
                    رجوع
                </a>
            </div>
        </form>
    </section>
@endsection

