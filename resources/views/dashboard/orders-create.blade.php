@extends('layouts.app')

@section('title', 'إنشاء طلب')
@section('page_title', 'إنشاء طلب')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">إنشاء طلب</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            أدخل بيانات العميل والمنتجات المطلوبة.
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

        {{-- بيانات العملاء المسجلين لـ JS --}}
        <script>
            const registeredCustomers = @json($customers->keyBy('phone'));
        </script>

        <form method="POST" action="{{ route('orders.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 14px;">

                {{-- تليفون العميل --}}
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">تليفون العميل</label>

                    @if ($customers->isNotEmpty())
                        <select id="phone-select" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; margin-bottom:8px;">
                            <option value="">-- اختر عميل مسجل --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->phone }}" data-name="{{ $c->name }}" data-address="{{ $c->address }}">
                                    {{ $c->phone }} — {{ $c->name }}
                                </option>
                            @endforeach
                            <option value="__new__">✏️ كتابة رقم جديد</option>
                        </select>
                    @endif

                    <input
                        id="phone-input"
                        name="phone"
                        type="text"
                        value="{{ old('phone') }}"
                        placeholder="اكتب رقم التليفون"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; {{ $customers->isNotEmpty() ? 'display:none;' : '' }}"
                    >
                </div>

                {{-- اسم العميل --}}
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">اسم العميل</label>
                    <input id="customer-name" name="customer_name" type="text" value="{{ old('customer_name') }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                {{-- عنوان التوصيل --}}
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">عنوان التوصيل</label>
                    <textarea id="delivery-address" name="delivery_address" rows="2" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;">{{ old('delivery_address') }}</textarea>
                </div>

                {{-- المنتجات --}}
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">المنتجات المطلوبة</label>
                    <textarea name="items" rows="4" required placeholder="مثال: عسل سدر 2 عبوة + عسل كشميري 1 عبوة" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;">{{ old('items') }}</textarea>
                </div>

                {{-- حالة الطلب --}}
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">حالة الطلب</label>
                    <select name="status" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                        <option value="">-- اختر --</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" {{ old('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-top: 14px;">
                <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                    حفظ الطلب
                </button>
                <a href="{{ route('orders.index') }}" style="display:inline-block; margin-inline-start: 10px; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 14px; border-radius:8px; font-weight:700;">
                    رجوع
                </a>
            </div>
        </form>
    </section>

    <script>
        const phoneSelect  = document.getElementById('phone-select');
        const phoneInput   = document.getElementById('phone-input');
        const nameInput    = document.getElementById('customer-name');
        const addressInput = document.getElementById('delivery-address');

        if (phoneSelect) {
            phoneSelect.addEventListener('change', function () {
                const val = this.value;

                if (val === '__new__') {
                    // اكتب رقم جديد
                    phoneInput.style.display = '';
                    phoneInput.value = '';
                    phoneInput.focus();
                    nameInput.value    = '';
                    addressInput.value = '';
                } else if (val === '') {
                    // لم يتم الاختيار
                    phoneInput.style.display = 'none';
                    phoneInput.value = '';
                    nameInput.value    = '';
                    addressInput.value = '';
                } else {
                    // عميل مسجل — ملّي البيانات تلقائي
                    const opt = this.options[this.selectedIndex];
                    phoneInput.style.display = 'none';
                    phoneInput.value   = val;
                    nameInput.value    = opt.dataset.name    || '';
                    addressInput.value = opt.dataset.address || '';
                }
            });
        }
    </script>
@endsection
