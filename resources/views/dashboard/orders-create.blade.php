@extends('layouts.app')

@section('title', 'إنشاء طلب')
@section('page_title', 'إنشاء طلب')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">إنشاء طلب</h2>

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
            const registeredCustomers = @json($customers->keyBy('remote_jid'));
        </script>

        <form method="POST" action="{{ route('orders.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 14px;">

                {{-- remoteJid --}}
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">remoteJid</label>

                    @if ($customers->isNotEmpty())
                        <select id="remote-jid-select" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; margin-bottom:8px;">
                            <option value="">-- اختر عميل مسجل --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->remote_jid }}" data-address="{{ $c->address }}">
                                    {{ $c->remote_jid }}{{ $c->name ? ' — ' . $c->name : '' }}
                                </option>
                            @endforeach
                            <option value="__new__">✏️ إدخال remoteJid يدوياً</option>
                        </select>
                    @endif

                    <input
                        id="remote-jid-input"
                        name="remote_jid"
                        type="text"
                        value="{{ old('remote_jid') }}"
                        placeholder="مثال: 96550000000@s.whatsapp.net"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:monospace; font-size:14px; direction:ltr; {{ $customers->isNotEmpty() ? 'display:none;' : '' }}"
                    >
                </div>

                {{-- عنوان التوصيل --}}
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">عنوان التوصيل</label>
                    <textarea id="delivery-address" name="delivery_address" rows="2" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;">{{ old('delivery_address') }}</textarea>
                </div>

                {{-- المنتجات --}}
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">المنتجات المطلوبة <span style="color:#b91c1c;">*</span></label>
                    <textarea name="items" rows="4" required placeholder="مثال: عسل سدر 2 عبوة + عسل كشميري 1 عبوة" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;">{{ old('items') }}</textarea>
                </div>

                {{-- حالة الطلب --}}
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">حالة الطلب <span style="color:#b91c1c;">*</span></label>
                    <select name="status" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                        <option value="">-- اختر --</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" {{ old('status', \App\Models\Order::DEFAULT_STATUS) === $st ? 'selected' : '' }}>{{ $st }}</option>
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
        const remoteJidSelect  = document.getElementById('remote-jid-select');
        const remoteJidInput   = document.getElementById('remote-jid-input');
        const addressInput     = document.getElementById('delivery-address');

        if (remoteJidSelect) {
            remoteJidSelect.addEventListener('change', function () {
                const val = this.value;

                if (val === '__new__') {
                    remoteJidInput.style.display = '';
                    remoteJidInput.value = '';
                    remoteJidInput.focus();
                    addressInput.value = '';
                } else if (val === '') {
                    remoteJidInput.style.display = 'none';
                    remoteJidInput.value = '';
                    addressInput.value = '';
                } else {
                    const opt = this.options[this.selectedIndex];
                    remoteJidInput.style.display = 'none';
                    remoteJidInput.value   = val;
                    addressInput.value     = opt.dataset.address || '';
                }
            });
        }
    </script>
@endsection
