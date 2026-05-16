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

        <form method="POST" action="{{ route('orders.update', $order) }}">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 14px;">
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">remoteJid</label>
                    <input name="remote_jid" type="text" value="{{ old('remote_jid', $order->remote_jid) }}"
                           placeholder="مثال: 96550000000@s.whatsapp.net"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:monospace; font-size:14px; direction:ltr;">
                </div>
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
                        سيتم إرسال رسالة على الـ remoteJid بحالة الطلب الجديدة
                        @if($order->remote_jid)
                            — <span style="font-family:monospace; direction:ltr; display:inline-block;">{{ $order->remote_jid }}</span>
                        @else
                            <span style="color:#ef4444;">(لا يوجد remoteJid مرتبط بهذا الطلب)</span>
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

