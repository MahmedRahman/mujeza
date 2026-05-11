@extends('layouts.app')

@section('title', 'تعديل بيانات المستخدم')
@section('page_title', 'تعديل بيانات المستخدم')

@section('content')
    <section class="card" style="max-width: 540px;">
        <h2 style="margin-top: 0; font-weight: 700;">تعديل بيانات المستخدم</h2>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ─── الرد الآلي (AJAX فوري) ─── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; background:#f8f9fa; border:1px solid #e5e7eb; border-radius:10px; padding:14px 16px; margin-bottom:20px;">
            <div>
                <div style="font-weight:700; font-size:14px; color:#111827; margin-bottom:2px;">🤖 الرد الآلي لهذا الرقم</div>
                <div style="font-size:12px; color:#6b7280;">
                    الإعداد العام: <strong>{{ $globalEnabled ? 'مُفعَّل' : 'موقوف' }}</strong>
                    @if($hasOverride)
                        — <span style="color:#d4af37; font-weight:700;">إعداد خاص مفعَّل لهذا الرقم •</span>
                    @else
                        — يرث الإعداد العام
                    @endif
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                <span id="editToggleLabel" style="font-size:13px; font-weight:700; color:{{ $autoReply ? '#16a34a' : '#9ca3af' }};">
                    {{ $autoReply ? 'يرد' : 'لا يرد' }}
                </span>
                <button type="button" id="editToggleBtn"
                        data-chat-id="{{ $chatId }}"
                        data-enabled="{{ $autoReply ? 'true' : 'false' }}"
                        onclick="toggleEdit()"
                        style="position:relative; width:50px; height:26px; border-radius:26px; border:none; cursor:pointer; transition:background 0.2s; background:{{ $autoReply ? '#16a34a' : '#d1d5db' }}; padding:0; flex-shrink:0;">
                    <span id="editToggleThumb" style="position:absolute; top:3px; width:20px; height:20px; background:#fff; border-radius:50%; transition:left 0.2s; left:{{ $autoReply ? '27px' : '3px' }}; box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('customers.update', $customer->phone) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight: 700; margin-bottom: 6px;">
                    الرقم
                    <span style="font-weight:400; font-size:12px; color:#6b7280;">(معرّف ثابت - لا يمكن تغييره)</span>
                </label>
                <input
                    type="text"
                    value="{{ $customer->phone }}"
                    disabled
                    style="width:100%; box-sizing:border-box; border:1px solid #e5e7eb; border-radius:8px; padding:10px 12px; font-family:monospace; font-size:15px; background:#f9fafb; color:#6b7280; direction:ltr;"
                >
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-weight: 700; margin-bottom: 6px;">remoteJid</label>
                <input
                    type="text"
                    name="remote_jid"
                    value="{{ old('remote_jid', $customer->remote_jid) }}"
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
                    value="{{ old('name', $customer->name) }}"
                    required
                    style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-family:inherit; font-size:15px;"
                >
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display:block; font-weight: 700; margin-bottom: 6px;">العنوان</label>
                <textarea
                    name="address"
                    rows="3"
                    style="width:100%; box-sizing:border-box; border:1px solid #d1d5db; border-radius:8px; padding:10px 12px; font-family:inherit; font-size:15px; resize:vertical;"
                >{{ old('address', $customer->address) }}</textarea>
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                <button type="submit"
                        style="border:none; background:#d4af37; color:#111827; padding:11px 24px; border-radius:8px; font-weight:800; font-family:inherit; font-size:15px; cursor:pointer;">
                    حفظ التعديلات
                </button>
                <a href="{{ route('customers.index') }}"
                   style="text-decoration:none; color:#6b7280; padding:11px 16px; border-radius:8px; font-weight:600;">
                    إلغاء
                </a>
            </div>
        </form>
    </section>

<script>
async function toggleEdit() {
    const btn   = document.getElementById('editToggleBtn');
    const thumb = document.getElementById('editToggleThumb');
    const label = document.getElementById('editToggleLabel');
    const chatId = btn.dataset.chatId;
    const isOn   = btn.dataset.enabled === 'true';
    const newVal = !isOn;

    btn.disabled = true; btn.style.opacity = '0.6';

    try {
        const res = await fetch('{{ route('whatsapp.auto_reply.chat') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ chat_id: chatId, enabled: newVal }),
        });
        if (!res.ok) throw new Error();

        btn.dataset.enabled  = newVal ? 'true' : 'false';
        btn.style.background = newVal ? '#16a34a' : '#d1d5db';
        thumb.style.left     = newVal ? '27px' : '3px';
        label.textContent    = newVal ? 'يرد' : 'لا يرد';
        label.style.color    = newVal ? '#16a34a' : '#9ca3af';

        // تحديث نص الإعداد الخاص
        const note = btn.closest('div')?.previousElementSibling?.querySelector('div:last-child');
        if (note) {
            note.innerHTML = note.innerHTML.replace(/يرث الإعداد العام|إعداد خاص مفعَّل.*/,
                '<span style="color:#d4af37;font-weight:700;">إعداد خاص مفعَّل لهذا الرقم •</span>');
        }
    } catch {
        alert('حدث خطأ أثناء تغيير الرد الآلي.');
    } finally {
        btn.disabled = false; btn.style.opacity = '1';
    }
}
</script>
@endsection
