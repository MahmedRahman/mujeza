@extends('layouts.app')

@section('title', 'الأمراض')
@section('page_title', 'الأمراض')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">قائمة الأمراض</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">يمكنك إدارة الأمراض من هنا، ولإضافة مرض جديد ادخل على صفحة الإضافة.</p>
        <a href="{{ route('diseases.create') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
            + إضافة مرض جديد
        </a>

        @if ($diseases->isEmpty())
            <p style="margin-bottom: 0; color: #6b7280;">لا توجد أمراض مضافة بعد.</p>
        @else
            <div style="display: grid; gap: 12px;">
                @foreach ($diseases as $disease)
                    <article style="border: 1px solid #efe3b7; border-radius: 12px; padding: 12px; display:grid; grid-template-columns: 90px 1fr; gap: 12px;">
                        <div>
                            @if ($disease->image)
                                <img src="{{ asset('storage/' . $disease->image) }}" alt="{{ $disease->name }}" style="width: 90px; height: 90px; object-fit: cover; border-radius: 10px;">
                            @else
                                <div style="width: 90px; height: 90px; border-radius: 10px; background: #f3f4f6; display:flex; align-items:center; justify-content:center; color:#6b7280; font-size:12px;">
                                    بدون صورة
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 style="margin: 0 0 6px; font-weight: 700;">{{ $disease->name }}</h3>
                            <p style="margin: 0; color:#4b5563;">{{ $disease->description ?: 'لا يوجد وصف.' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
