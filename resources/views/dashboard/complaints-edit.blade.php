@extends('layouts.app')

@section('title', 'تعديل شكوى/استفسار')
@section('page_title', 'تعديل شكوى/استفسار')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">تعديل شكوى/استفسار</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            عدل البيانات ثم احفظ التغييرات.
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

        <form method="POST" action="{{ route('complaints.update', $complaint) }}">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">العنوان</label>
                    <input name="title" type="text" value="{{ old('title', $complaint->title) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">رقم التليفون</label>
                    <input name="phone" type="text" value="{{ old('phone', $complaint->phone) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">الوصف</label>
                <textarea name="description" rows="6" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('description', $complaint->description) }}</textarea>
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

