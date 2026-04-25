@extends('layouts.app')

@section('title', 'إضافة مرض')
@section('page_title', 'إضافة مرض جديد')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">إضافة مرض جديد</h2>
        <a href="{{ route('diseases.index') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:8px 14px; border-radius:8px; font-weight:700;">
            رجوع إلى قائمة الأمراض
        </a>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('diseases.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 12px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">اسم المرض</label>
                    <input name="name" type="text" value="{{ old('name') }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">الصورة</label>
                    <input name="image" type="file" accept="image/*" style="width:100%;">
                </div>
            </div>

            <div style="margin-top:12px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">الوصف</label>
                <textarea name="description" rows="3" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('description') }}</textarea>
            </div>

            <button type="submit" style="margin-top:14px; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                إضافة المرض
            </button>
        </form>
    </section>
@endsection
