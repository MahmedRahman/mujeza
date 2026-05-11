@extends('layouts.app')

@section('title', 'المستخدمين')
@section('page_title', 'المستخدمين')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">المستخدمين المسجلين</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            من هنا تقدر تراجع كل المستخدمين وتضيف مستخدمين جدد. الرقم هو المعرّف الأساسي ولا يمكن تغييره.
        </p>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── بطاقة الرد الآلي العام ─── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; background:#f8f9fa; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; margin-bottom:20px;">
            <div>
                <div style="font-weight:800; font-size:15px; color:#111827; margin-bottom:3px;">🤖 الرد الآلي العام</div>
                <div style="font-size:13px; color:#6b7280;">يسري على كل المستخدمين ما لم يكن هناك إعداد خاص برقمه</div>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <span id="autoReplyLabel" style="font-weight:700; font-size:14px; color: {{ $autoReplyEnabled ? '#16a34a' : '#6b7280' }};">
                    {{ $autoReplyEnabled ? 'مُفعَّل' : 'موقوف' }}
                </span>
                <button
                    id="autoReplyToggle"
                    onclick="toggleGlobalAutoReply()"
                    data-enabled="{{ $autoReplyEnabled ? 'true' : 'false' }}"
                    style="position:relative; display:inline-block; width:56px; height:30px; border-radius:30px; border:none; cursor:pointer; transition:background 0.25s; background:{{ $autoReplyEnabled ? '#16a34a' : '#d1d5db' }}; padding:0; flex-shrink:0;"
                >
                    <span id="autoReplyThumb" style="position:absolute; top:3px; width:24px; height:24px; background:#fff; border-radius:50%; transition:left 0.25s; left:{{ $autoReplyEnabled ? '29px' : '3px' }}; box-shadow:0 1px 4px rgba(0,0,0,0.2);"></span>
                </button>
            </div>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="{{ route('customers.create') }}"
               style="display:inline-block; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
                + إضافة مستخدم جديد
            </a>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px;">
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">إجمالي المستخدمين</div>
                <div style="font-size:26px; font-weight:900; color:#111827;">{{ $totalCount }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('customers.index') }}" style="margin-bottom: 14px; display:flex; gap: 10px; flex-wrap:wrap; align-items:center;">
            <input
                name="q"
                type="text"
                value="{{ $q ?? '' }}"
                placeholder="بحث بالاسم أو الرقم أو العنوان"
                style="width: 320px; max-width: 100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;"
            >
            <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:800; font-family:inherit; cursor:pointer;">
                بحث
            </button>
            @if (($q ?? '') !== '')
                <a href="{{ route('customers.index') }}" style="display:inline-block; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 14px; border-radius:8px; font-weight:800;">
                    إلغاء البحث
                </a>
            @endif
        </form>

        @if ($customers->isEmpty())
            <p style="margin-bottom: 0; color: #6b7280;">لا يوجد مستخدمون مسجلون بعد.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 720px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">تاريخ التسجيل</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الاسم</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الرقم</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">remoteJid</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">العنوان</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: center;">الرد الآلي</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            @php
                                $chatId  = $customer->phone . '@s.whatsapp.net';
                                $hasOverride = array_key_exists($chatId, $chatOverrides);
                                $customerAutoReply = $hasOverride ? (bool) $chatOverrides[$chatId] : $autoReplyEnabled;
                            @endphp
                            <tr>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color: #6b7280; font-size: 13px; white-space:nowrap;">
                                    {{ $customer->created_at?->format('Y-m-d') }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight: 600;">{{ $customer->name }}</td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight: 700; direction: ltr; text-align: right;">
                                    <span style="background:#f1f5f9; border-radius:6px; padding:3px 8px; font-family:monospace;">{{ $customer->phone }}</span>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#4b5563; font-size:13px; direction:ltr; text-align:right;">
                                    <span style="font-family:monospace;">{{ $customer->remote_jid ?? '—' }}</span>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color: #4b5563;">{{ $customer->address ?? '—' }}</td>

                                {{-- ─── Toggle الرد الآلي لكل مستخدم ─── --}}
                                <td style="padding: 10px; border: 1px solid #efe3b7; text-align:center;">
                                    <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                                        <button
                                            class="chat-toggle"
                                            data-chat-id="{{ $chatId }}"
                                            data-enabled="{{ $customerAutoReply ? 'true' : 'false' }}"
                                            data-overridden="{{ $hasOverride ? 'true' : 'false' }}"
                                            onclick="toggleChatAutoReply(this)"
                                            style="position:relative; width:46px; height:24px; border-radius:24px; border:none; cursor:pointer; transition:background 0.2s; background:{{ $customerAutoReply ? '#16a34a' : '#d1d5db' }}; padding:0; flex-shrink:0;"
                                        >
                                            <span style="position:absolute; top:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:left 0.2s; left:{{ $customerAutoReply ? '25px' : '3px' }}; box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                                        </button>
                                        <span class="chat-toggle-label" style="font-size:11px; font-weight:700; color:{{ $customerAutoReply ? '#16a34a' : '#9ca3af' }};">
                                            {{ $customerAutoReply ? 'يرد' : 'لا يرد' }}
                                            @if($hasOverride)
                                                <span style="color:#d4af37;" title="إعداد خاص بهذا الرقم">•</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <a href="{{ route('customers.edit', $customer->phone) }}"
                                           style="text-decoration:none; background:#f8f2de; color:#92400e; border:1px solid #d4af37; padding:6px 12px; border-radius:6px; font-weight:700; font-size:13px;">
                                            تعديل
                                        </a>
                                        <form method="POST" action="{{ route('customers.destroy', $customer->phone) }}"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="border:1px solid #fca5a5; background:#fef2f2; color:#b91c1c; padding:6px 12px; border-radius:6px; font-weight:700; font-size:13px; font-family:inherit; cursor:pointer;">
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

            <div style="margin-top: 16px;">
                {{ $customers->links() }}
            </div>
        @endif
    </section>

<script>
const CSRF = '{{ csrf_token() }}';
const GLOBAL_URL = '{{ route('whatsapp.auto_reply.global') }}';
const CHAT_URL   = '{{ route('whatsapp.auto_reply.chat') }}';

/* ── الرد الآلي العام ── */
async function toggleGlobalAutoReply() {
    const btn   = document.getElementById('autoReplyToggle');
    const thumb = document.getElementById('autoReplyThumb');
    const label = document.getElementById('autoReplyLabel');
    const isOn  = btn.dataset.enabled === 'true';
    const newVal = !isOn;

    btn.disabled = true; btn.style.opacity = '0.6';

    try {
        const res = await fetch(GLOBAL_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ enabled: newVal }),
        });
        if (!res.ok) throw new Error();

        btn.dataset.enabled  = newVal ? 'true' : 'false';
        btn.style.background = newVal ? '#16a34a' : '#d1d5db';
        thumb.style.left     = newVal ? '29px' : '3px';
        label.textContent    = newVal ? 'مُفعَّل' : 'موقوف';
        label.style.color    = newVal ? '#16a34a' : '#6b7280';

        /* تحديث الأرقام اللي ما عندهاش override تلقائياً */
        document.querySelectorAll('.chat-toggle[data-overridden="false"]').forEach(t => {
            applyToggleState(t, newVal);
        });
    } catch {
        alert('حدث خطأ أثناء تغيير الرد الآلي العام.');
    } finally {
        btn.disabled = false; btn.style.opacity = '1';
    }
}

/* ── الرد الآلي لكل مستخدم ── */
async function toggleChatAutoReply(btn) {
    const chatId = btn.dataset.chatId;
    const isOn   = btn.dataset.enabled === 'true';
    const newVal = !isOn;

    btn.disabled = true; btn.style.opacity = '0.6';

    try {
        const res = await fetch(CHAT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ chat_id: chatId, enabled: newVal }),
        });
        if (!res.ok) throw new Error();

        btn.dataset.enabled    = newVal ? 'true' : 'false';
        btn.dataset.overridden = 'true';
        applyToggleState(btn, newVal, true);
    } catch {
        alert('حدث خطأ أثناء تغيير الرد الآلي لهذا الرقم.');
    } finally {
        btn.disabled = false; btn.style.opacity = '1';
    }
}

function applyToggleState(btn, enabled, isOverride = false) {
    btn.style.background = enabled ? '#16a34a' : '#d1d5db';
    const thumb = btn.querySelector('span');
    if (thumb) thumb.style.left = enabled ? '25px' : '3px';

    const label = btn.closest('td')?.querySelector('.chat-toggle-label');
    if (label) {
        label.style.color = enabled ? '#16a34a' : '#9ca3af';
        label.innerHTML   = (enabled ? 'يرد' : 'لا يرد') +
            (isOverride || btn.dataset.overridden === 'true'
                ? ' <span style="color:#d4af37;" title="إعداد خاص بهذا الرقم">•</span>'
                : '');
    }
}
</script>

@endsection
