@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد')
@section('page_title', 'إضافة مستخدم جديد')

@section('content')
    <section class="card" style="max-width: 540px;">
        <h2 style="margin-top: 0; font-weight: 700;">إضافة مستخدم جديد</h2>
        <p style="color: #4b5563; margin-bottom: 20px;">الرقم هو المعرّف الأساسي للمستخدم ولا يمكن تغييره بعد الإضافة.</p>

        @if ($errors->any())
            <div style="background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight: 700; margin-bottom: 6px;">
                    الرقم <span style="color:#b91c1c;">*</span>
                    <span style="font-weight:400; font-size:12px; color:#6b7280;">(معرّف ثابت - لا يمكن تغييره)</span>
                </label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="مثال: 01012345678"
                    required
                    style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-family:inherit; font-size:15px; direction:ltr;"
                >
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight: 700; margin-bottom: 6px;">remoteJid</label>
                <input
                    type="text"
                    name="remote_jid"
                    value="{{ old('remote_jid') }}"
                    placeholder="مثال: 96550000000@s.whatsapp.net"
                    style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-family:monospace; font-size:14px; direction:ltr;"
                >
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight: 700; margin-bottom: 6px;">
                    الاسم <span style="color:#b91c1c;">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="اسم المستخدم"
                    required
                    style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-family:inherit; font-size:15px;"
                >
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight: 700; margin-bottom: 6px;">العنوان</label>
                <textarea
                    name="address"
                    rows="3"
                    placeholder="عنوان المستخدم (اختياري)"
                    style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-family:inherit; font-size:15px; resize:vertical;"
                >{{ old('address') }}</textarea>
            </div>

            {{-- ─── الرد الآلي ─── --}}
            @php $defaultOn = old('auto_reply', $globalAutoReply ? '1' : '0') === '1'; @endphp
            <input type="hidden" name="auto_reply" id="autoReplyInput" value="{{ $defaultOn ? '1' : '0' }}">

            <div style="display:flex; align-items:center; justify-content:space-between; background:#f8f9fa; border:1px solid #e5e7eb; border-radius:10px; padding:14px 16px; margin-bottom:24px;">
                <div>
                    <div style="font-weight:700; font-size:14px; color:#111827; margin-bottom:2px;">🤖 الرد الآلي لهذا الرقم</div>
                    <div style="font-size:12px; color:#6b7280;">
                        الإعداد العام حالياً: <strong>{{ $globalAutoReply ? 'مُفعَّل' : 'موقوف' }}</strong>
                        — يمكنك تخصيص إعداد مختلف لهذا الرقم
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                    <span id="createToggleLabel" style="font-size:13px; font-weight:700; color:{{ $defaultOn ? '#16a34a' : '#9ca3af' }};">
                        {{ $defaultOn ? 'يرد' : 'لا يرد' }}
                    </span>
                    <button type="button" id="createToggleBtn" onclick="toggleCreate()"
                            style="position:relative; width:50px; height:26px; border-radius:26px; border:none; cursor:pointer; transition:background 0.2s; background:{{ $defaultOn ? '#16a34a' : '#d1d5db' }}; padding:0; flex-shrink:0;">
                        <span id="createToggleThumb" style="position:absolute; top:3px; width:20px; height:20px; background:#fff; border-radius:50%; transition:left 0.2s; left:{{ $defaultOn ? '27px' : '3px' }}; box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                    </button>
                </div>
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                <button type="submit"
                        style="border:none; background:#d4af37; color:#111827; padding:11px 24px; border-radius:8px; font-weight:800; font-family:inherit; font-size:15px; cursor:pointer;">
                    إضافة المستخدم
                </button>
                <a href="{{ route('customers.index') }}"
                   style="text-decoration:none; color:#6b7280; padding:11px 16px; border-radius:8px; font-weight:600;">
                    إلغاء
                </a>
            </div>
        </form>
    </section>

<script>
function toggleCreate() {
    const input = document.getElementById('autoReplyInput');
    const btn   = document.getElementById('createToggleBtn');
    const thumb = document.getElementById('createToggleThumb');
    const label = document.getElementById('createToggleLabel');
    const isOn  = input.value === '1';
    const newOn = !isOn;

    input.value          = newOn ? '1' : '0';
    btn.style.background = newOn ? '#16a34a' : '#d1d5db';
    thumb.style.left     = newOn ? '27px' : '3px';
    label.textContent    = newOn ? 'يرد' : 'لا يرد';
    label.style.color    = newOn ? '#16a34a' : '#9ca3af';
}
</script>
@endsection
