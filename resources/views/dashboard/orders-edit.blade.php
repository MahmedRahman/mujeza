@extends('layouts.app')

@section('title', 'تعديل طلب')
@section('page_title', 'تعديل طلب')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">تعديل الطلب #{{ $order->order_number }}</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            عدل بيانات العميل والحالة.
        </p>

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
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">اسم العميل</label>
                    <input name="customer_name" type="text" value="{{ old('customer_name', $order->customer_name) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">تليفون العميل</label>
                    <input name="phone" type="text" value="{{ old('phone', $order->phone) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">عنوان التوصيل</label>
                    <textarea name="delivery_address" rows="2" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;">{{ old('delivery_address', $order->delivery_address) }}</textarea>
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

