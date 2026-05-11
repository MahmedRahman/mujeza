@extends('layouts.app')

@section('title', 'تعديل شكوى')
@section('page_title', 'تعديل شكوى')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">تعديل شكوى</h2>

        @if ($errors->any())
            <div style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('complaints.update', $complaint) }}">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">remoteJid</label>
                    <input name="remote_jid" type="text" value="{{ old('remote_jid', $complaint->remote_jid) }}"
                           placeholder="مثال: 96550000000@s.whatsapp.net"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:monospace; font-size:14px; direction:ltr;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">العنوان <span style="color:#b91c1c;">*</span></label>
                    <input name="title" type="text" value="{{ old('title', $complaint->title) }}" required
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">الحالة <span style="color:#b91c1c;">*</span></label>
                    <select name="status" required
                            style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; background:#fff;">
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" {{ old('status', $complaint->status ?? 'جديدة') === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">الوصف <span style="color:#b91c1c;">*</span></label>
                <textarea name="description" rows="6" required
                          style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('description', $complaint->description) }}</textarea>
            </div>

            {{-- إشعار واتساب --}}
            <div style="border:1px solid #d1fae5; border-radius:10px; padding:14px 16px; background:#f0fdf4; margin-bottom:14px; display:flex; align-items:flex-start; gap:12px;">
                <input type="checkbox" name="notify_customer" value="1" id="notify_complaint"
                       style="margin-top:3px; width:18px; height:18px; accent-color:#16a34a; flex-shrink:0; cursor:pointer;">
                <label for="notify_complaint" style="cursor:pointer; line-height:1.5;">
                    <span style="font-weight:800; color:#15803d; font-size:14px;">📲 إرسال إشعار واتساب للعميل</span>
                    <div style="font-size:12px; color:#4b5563; margin-top:3px;">
                        سيتم إرسال رسالة بتحديث حالة الشكوى على الـ remoteJid
                        @if($complaint->remote_jid)
                            — <span style="font-family:monospace; direction:ltr; display:inline-block;">{{ $complaint->remote_jid }}</span>
                        @else
                            <span style="color:#ef4444;">(لا يوجد remoteJid مرتبط بهذه الشكوى)</span>
                        @endif
                    </div>
                </label>
            </div>

            <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                حفظ التعديلات
            </button>
            <a href="{{ route('complaints.index') }}" style="display:inline-block; margin-inline-start:10px; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 14px; border-radius:8px; font-weight:700;">
                رجوع
            </a>
        </form>
    </section>
@endsection
