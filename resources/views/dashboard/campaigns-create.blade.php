@extends('layouts.app')

@section('title', 'إنشاء حملة إعلانية')
@section('page_title', 'إنشاء حملة إعلانية')

@section('content')
<section class="card">
    <h2 style="margin-top:0; font-weight:700;">📣 إنشاء حملة إعلانية جديدة</h2>
    <p style="color:#4b5563; margin-bottom:20px;">
        اختر المستخدمين، اكتب اسم الحملة والرسالة، واضغط إرسال. هتشوف حالة كل رقم بعد الإرسال.
        <strong style="color:#b91c1c;">الحد الأقصى: {{ $phoneLimit }} رقم في الحملة الواحدة.</strong>
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

    <form method="POST" action="{{ route('campaigns.store') }}" id="campaignForm">
        @csrf

        {{-- اسم الحملة --}}
        <div style="margin-bottom:18px;">
            <label style="display:block; font-weight:700; margin-bottom:6px;">
                اسم الحملة <span style="color:#b91c1c;">*</span>
            </label>
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="مثال: حملة عيد الأضحى – مايو 2026"
                required
                style="width:100%; max-width:480px; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-family:inherit; font-size:15px;"
            >
        </div>

        {{-- أزرار التحديد والبحث --}}
        <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px; flex-wrap:wrap;">
            <button type="button" onclick="selectAll()"
                    style="border:1px solid #d4af37; background:#fffcf2; color:#92400e; padding:8px 16px; border-radius:7px; font-weight:700; font-family:inherit; cursor:pointer; font-size:13px;">
                ✓ تحديد الكل
            </button>
            <button type="button" onclick="deselectAll()"
                    style="border:1px solid #d1d5db; background:#fff; color:#374151; padding:8px 16px; border-radius:7px; font-weight:700; font-family:inherit; cursor:pointer; font-size:13px;">
                ✗ إلغاء تحديد الكل
            </button>
            <span id="selectedCount" style="color:#6b7280; font-size:13px; font-weight:600; background:#f3f4f6; padding:6px 12px; border-radius:20px;">
                0 مختار
            </span>
            <span style="color:#d1d5db;">|</span>
            <span style="color:#6b7280; font-size:13px;">الحد: <strong style="color:#b91c1c;">{{ $phoneLimit }}</strong></span>
        </div>

        <input
            type="text"
            id="customerSearch"
            placeholder="بحث سريع بالاسم أو الرقم..."
            oninput="filterCustomers()"
            style="width:100%; max-width:360px; border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-family:inherit; margin-bottom:12px; box-sizing:border-box;"
        >

        {{-- جدول الاختيار --}}
        @if ($customers->isEmpty())
            <div style="padding:20px; background:#f9fafb; border-radius:8px; color:#6b7280; text-align:center; margin-bottom:18px;">
                لا يوجد مستخدمون مسجلون بعد. أضف مستخدمين أولاً من صفحة المستخدمين.
            </div>
        @else
            <div style="overflow-x:auto; margin-bottom:20px; border:1px solid #e5e7eb; border-radius:10px; max-height:420px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:420px;" id="customersTable">
                    <thead style="position:sticky; top:0; z-index:1;">
                        <tr style="background:#f8f2de;">
                            <th style="padding:10px; border-bottom:1px solid #efe3b7; width:44px; text-align:center;">
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
                                style="border-bottom:1px solid #f3f4f6; cursor:pointer; transition:background 0.1s;">
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
                                    <span style="font-family:monospace; background:#f1f5f9; border-radius:5px; padding:2px 7px; font-size:13px;">{{ $customer->phone }}</span>
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
                rows="6"
                placeholder="اكتب الرسالة التي ستُرسَل لكل المستخدمين المحددين..."
                required
                oninput="updateCharCount()"
                style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:12px; font-family:inherit; font-size:15px; resize:vertical;"
            >{{ old('message') }}</textarea>
            <div style="text-align:left; font-size:12px; color:#9ca3af; margin-top:4px;">
                <span id="charCount">0</span> / 4000 حرف
            </div>
        </div>

        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <button type="submit" id="sendBtn" onclick="return confirmSend()"
                    style="border:none; background:#16a34a; color:#fff; padding:12px 28px; border-radius:8px; font-weight:800; font-family:inherit; font-size:15px; cursor:pointer;">
                📤 إرسال الحملة
            </button>
            <a href="{{ route('campaigns.index') }}"
               style="text-decoration:none; color:#6b7280; padding:12px 16px; border-radius:8px; font-weight:600; font-size:15px;">
                إلغاء
            </a>
        </div>
    </form>
</section>

<script>
const PHONE_LIMIT = {{ $phoneLimit }};

function selectAll() {
    let count = 0;
    document.querySelectorAll('.customer-row').forEach(row => {
        if (row.style.display === 'none') return;
        const cb = row.querySelector('.phone-check');
        if (count < PHONE_LIMIT) { cb.checked = true; count++; }
        else cb.checked = false;
    });
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.phone-check').forEach(cb => cb.checked = false);
    document.getElementById('masterCheck').checked = false;
    updateCount();
}

function toggleAll(master) {
    let count = 0;
    document.querySelectorAll('.customer-row').forEach(row => {
        if (row.style.display === 'none') return;
        const cb = row.querySelector('.phone-check');
        if (master.checked && count < PHONE_LIMIT) { cb.checked = true; count++; }
        else if (!master.checked) cb.checked = false;
    });
    updateCount();
}

function updateCount() {
    const n = document.querySelectorAll('.phone-check:checked').length;
    const el = document.getElementById('selectedCount');
    el.textContent = n + ' مختار';
    el.style.background = n > PHONE_LIMIT ? '#fee2e2' : '#f3f4f6';
    el.style.color = n > PHONE_LIMIT ? '#b91c1c' : '#6b7280';
}

function filterCustomers() {
    const q = document.getElementById('customerSearch').value.toLowerCase().trim();
    document.querySelectorAll('.customer-row').forEach(row => {
        const match = !q || row.dataset.name.includes(q) || row.dataset.phone.includes(q);
        row.style.display = match ? '' : 'none';
    });
}

function updateCharCount() {
    document.getElementById('charCount').textContent = document.getElementById('messageText').value.length;
}

function confirmSend() {
    const n = document.querySelectorAll('.phone-check:checked').length;
    if (n === 0) { alert('من فضلك اختر رقم واحد على الأقل.'); return false; }
    if (n > PHONE_LIMIT) { alert('تجاوزت الحد المسموح به (' + PHONE_LIMIT + ' رقم). قلّل الاختيار.'); return false; }
    if (!document.getElementById('messageText').value.trim()) { alert('من فضلك اكتب نص الرسالة.'); return false; }
    return confirm('هيتم إرسال الحملة لـ ' + n + ' رقم. هل أنت متأكد؟');
}

// Hover + click-to-select on rows
document.querySelectorAll('.customer-row').forEach(row => {
    row.addEventListener('click', e => {
        if (e.target.tagName === 'INPUT') return;
        const cb = row.querySelector('.phone-check');
        cb.checked = !cb.checked;
        updateCount();
    });
    row.addEventListener('mouseenter', () => row.style.background = '#f8f9fa');
    row.addEventListener('mouseleave', () => row.style.background = '');
});
</script>
@endsection
