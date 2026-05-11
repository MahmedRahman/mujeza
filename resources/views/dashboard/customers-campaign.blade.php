@extends('layouts.app')

@section('title', 'إنشاء حملة دعائية')
@section('page_title', 'إنشاء حملة دعائية')

@section('content')

{{-- ─── نتائج الإرسال ─── --}}
@isset($results)
<section class="card" style="margin-bottom: 24px;">
    <h2 style="margin-top:0; font-weight:700; display:flex; align-items:center; gap:10px;">
        نتائج الإرسال
        <span style="font-size:14px; font-weight:600; color:#6b7280;">
            ({{ collect($results)->where('status','success')->count() }} / {{ count($results) }} نجحت)
        </span>
    </h2>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:480px;">
            <thead>
                <tr style="background:#f8f2de;">
                    <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">#</th>
                    <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الاسم</th>
                    <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الرقم</th>
                    <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الحالة</th>
                    <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">التفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($results as $i => $r)
                    <tr style="background: {{ $r['status'] === 'success' ? '#f0fdf4' : '#fef2f2' }}">
                        <td style="padding:10px; border:1px solid #efe3b7; color:#9ca3af; font-size:13px;">{{ $i + 1 }}</td>
                        <td style="padding:10px; border:1px solid #efe3b7; font-weight:600;">{{ $r['name'] }}</td>
                        <td style="padding:10px; border:1px solid #efe3b7; direction:ltr; text-align:right;">
                            <span style="font-family:monospace; background:#f1f5f9; border-radius:5px; padding:2px 7px;">{{ $r['phone'] }}</span>
                        </td>
                        <td style="padding:10px; border:1px solid #efe3b7;">
                            @if ($r['status'] === 'success')
                                <span style="background:#dcfce7; color:#166534; border-radius:20px; padding:3px 12px; font-weight:700; font-size:13px;">✓ نجح</span>
                            @else
                                <span style="background:#fee2e2; color:#b91c1c; border-radius:20px; padding:3px 12px; font-weight:700; font-size:13px;">✗ فشل</span>
                            @endif
                        </td>
                        <td style="padding:10px; border:1px solid #efe3b7; font-size:13px; color:#4b5563;">{{ $r['message'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px; padding:12px 14px; background:#f8f9fa; border-radius:8px; border:1px solid #e5e7eb;">
        <div style="font-weight:700; margin-bottom:4px; color:#374151;">الرسالة المُرسَلة:</div>
        <div style="white-space:pre-wrap; color:#4b5563; font-size:14px;">{{ $message ?? '' }}</div>
    </div>

    <div style="margin-top:16px;">
        <a href="{{ route('customers.campaign') }}"
           style="text-decoration:none; background:#16a34a; color:#fff; padding:10px 20px; border-radius:8px; font-weight:700; display:inline-block;">
            📣 إنشاء حملة جديدة
        </a>
        <a href="{{ route('customers.index') }}"
           style="text-decoration:none; color:#6b7280; padding:10px 16px; border-radius:8px; font-weight:600; display:inline-block;">
            ← العودة للمستخدمين
        </a>
    </div>
</section>
@endisset

{{-- ─── فورم الحملة ─── --}}
@unless(isset($results))
<section class="card">
    <h2 style="margin-top:0; font-weight:700;">📣 إنشاء حملة دعائية على واتساب</h2>
    <p style="color:#4b5563; margin-bottom:20px;">
        اختر المستخدمين اللي عايز ترسلهم رسالة، اكتب الرسالة، واضغط إرسال. هتشوف حالة كل رقم بعد الإرسال.
    </p>

    @if ($errors->any())
        <div style="background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:10px; padding:10px 14px; margin-bottom:16px;">
            <ul style="margin:0; padding-right:16px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customers.campaign.send') }}" id="campaignForm">
        @csrf

        {{-- أزرار التحديد --}}
        <div style="display:flex; gap:10px; align-items:center; margin-bottom:14px; flex-wrap:wrap;">
            <button type="button" onclick="selectAll()"
                    style="border:1px solid #d4af37; background:#fffcf2; color:#92400e; padding:8px 16px; border-radius:7px; font-weight:700; font-family:inherit; cursor:pointer; font-size:13px;">
                ✓ تحديد الكل
            </button>
            <button type="button" onclick="deselectAll()"
                    style="border:1px solid #d1d5db; background:#fff; color:#374151; padding:8px 16px; border-radius:7px; font-weight:700; font-family:inherit; cursor:pointer; font-size:13px;">
                ✗ إلغاء تحديد الكل
            </button>
            <span id="selectedCount" style="color:#6b7280; font-size:13px; font-weight:600;">0 مختار</span>
        </div>

        {{-- البحث السريع --}}
        <input
            type="text"
            id="customerSearch"
            placeholder="بحث سريع بالاسم أو الرقم..."
            oninput="filterCustomers()"
            style="width:100%; max-width:360px; border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-family:inherit; margin-bottom:14px; box-sizing:border-box;"
        >

        {{-- جدول المستخدمين --}}
        @if ($customers->isEmpty())
            <p style="color:#6b7280;">لا يوجد مستخدمون مسجلون بعد.</p>
        @else
            <div style="overflow-x:auto; margin-bottom:20px; border:1px solid #e5e7eb; border-radius:10px;">
                <table style="width:100%; border-collapse:collapse; min-width:460px;" id="customersTable">
                    <thead>
                        <tr style="background:#f8f2de;">
                            <th style="padding:10px; border-bottom:1px solid #efe3b7; width:40px; text-align:center;">
                                <input type="checkbox" id="masterCheck" onchange="toggleAll(this)"
                                       style="width:16px; height:16px; cursor:pointer; accent-color:#16a34a;">
                            </th>
                            <th style="padding:10px; border-bottom:1px solid #efe3b7; text-align:right;">تاريخ التسجيل</th>
                            <th style="padding:10px; border-bottom:1px solid #efe3b7; text-align:right;">الاسم</th>
                            <th style="padding:10px; border-bottom:1px solid #efe3b7; text-align:right;">الرقم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr class="customer-row"
                                data-name="{{ mb_strtolower($customer->name) }}"
                                data-phone="{{ $customer->phone }}"
                                style="border-bottom:1px solid #f3f4f6; transition: background 0.15s;">
                                <td style="padding:10px; text-align:center;">
                                    <input
                                        type="checkbox"
                                        name="phones[]"
                                        value="{{ $customer->phone }}"
                                        class="phone-check"
                                        onchange="updateCount()"
                                        style="width:16px; height:16px; cursor:pointer; accent-color:#16a34a;"
                                    >
                                </td>
                                <td style="padding:10px; color:#6b7280; font-size:13px; white-space:nowrap;">
                                    {{ $customer->created_at?->format('Y-m-d') }}
                                </td>
                                <td style="padding:10px; font-weight:600;">{{ $customer->name }}</td>
                                <td style="padding:10px; direction:ltr; text-align:right;">
                                    <span style="font-family:monospace; background:#f1f5f9; border-radius:5px; padding:2px 7px;">{{ $customer->phone }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- نص الرسالة --}}
        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:700; margin-bottom:8px;">
                نص الرسالة <span style="color:#b91c1c;">*</span>
            </label>
            <textarea
                name="message"
                id="messageText"
                rows="5"
                placeholder="اكتب الرسالة اللي هتتبعت لكل المستخدمين المحددين..."
                required
                oninput="updateCharCount()"
                style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:12px; font-family:inherit; font-size:15px; resize:vertical;"
            >{{ old('message') }}</textarea>
            <div style="text-align:left; font-size:12px; color:#9ca3af; margin-top:4px;">
                <span id="charCount">0</span> / 4000 حرف
            </div>
        </div>

        {{-- زر الإرسال --}}
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <button type="submit" id="sendBtn" onclick="return confirmSend()"
                    style="border:none; background:#16a34a; color:#fff; padding:12px 28px; border-radius:8px; font-weight:800; font-family:inherit; font-size:15px; cursor:pointer;">
                📤 إرسال الحملة
            </button>
            <a href="{{ route('customers.index') }}"
               style="text-decoration:none; color:#6b7280; padding:12px 16px; border-radius:8px; font-weight:600; font-size:15px;">
                إلغاء
            </a>
        </div>
    </form>
</section>
@endunless

<script>
function selectAll() {
    document.querySelectorAll('.phone-check:not([style*="display: none"])').forEach(cb => {
        const row = cb.closest('tr');
        if (row && row.style.display !== 'none') cb.checked = true;
    });
    document.getElementById('masterCheck').checked = true;
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.phone-check').forEach(cb => cb.checked = false);
    document.getElementById('masterCheck').checked = false;
    updateCount();
}

function toggleAll(master) {
    document.querySelectorAll('.phone-check').forEach(cb => {
        const row = cb.closest('tr');
        if (row && row.style.display !== 'none') cb.checked = master.checked;
    });
    updateCount();
}

function updateCount() {
    const n = document.querySelectorAll('.phone-check:checked').length;
    document.getElementById('selectedCount').textContent = n + ' مختار';
}

function filterCustomers() {
    const q = document.getElementById('customerSearch').value.toLowerCase().trim();
    document.querySelectorAll('.customer-row').forEach(row => {
        const name  = row.dataset.name  || '';
        const phone = row.dataset.phone || '';
        row.style.display = (!q || name.includes(q) || phone.includes(q)) ? '' : 'none';
    });
    updateCount();
}

function updateCharCount() {
    const len = document.getElementById('messageText').value.length;
    document.getElementById('charCount').textContent = len;
}

function confirmSend() {
    const n = document.querySelectorAll('.phone-check:checked').length;
    if (n === 0) {
        alert('من فضلك اختر رقم واحد على الأقل.');
        return false;
    }
    const msg = document.getElementById('messageText').value.trim();
    if (!msg) {
        alert('من فضلك اكتب نص الرسالة.');
        return false;
    }
    return confirm('هيتم إرسال الرسالة لـ ' + n + ' رقم. هل أنت متأكد؟');
}

// إضافة تأثير hover على الصفوف
document.querySelectorAll('.customer-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.tagName === 'INPUT') return;
        const cb = this.querySelector('.phone-check');
        cb.checked = !cb.checked;
        updateCount();
    });
    row.style.cursor = 'pointer';
    row.addEventListener('mouseenter', () => row.style.background = '#f8f9fa');
    row.addEventListener('mouseleave', () => row.style.background = '');
});
</script>

@endsection
