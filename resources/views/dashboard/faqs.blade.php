@extends('layouts.app')

@section('title', 'الأسئلة الشائعة')
@section('page_title', 'الأسئلة الشائعة (FAQ)')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">الأسئلة الشائعة</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            إدارة الأسئلة والأجوبة التي تظهر للعملاء.
        </p>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <a href="{{ route('faqs.create') }}" style="display:inline-block; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
                + إضافة سؤال وجواب
            </a>
            <span style="color:#6b7280; font-weight:600; font-size:14px;">
                الإجمالي: {{ $faqs->count() }} سؤال
            </span>
        </div>

        @if ($faqs->isEmpty())
            <div style="text-align:center; padding: 40px 0; color:#6b7280;">
                <div style="font-size:40px; margin-bottom:10px;">❓</div>
                <p style="margin:0; font-weight:600;">لا توجد أسئلة شائعة بعد. أضف أول سؤال وجواب الآن.</p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px 12px; border: 1px solid #efe3b7; text-align: right; width: 40px;">#</th>
                            <th style="padding: 10px 12px; border: 1px solid #efe3b7; text-align: right;">السؤال</th>
                            <th style="padding: 10px 12px; border: 1px solid #efe3b7; text-align: right;">الجواب</th>
                            <th style="padding: 10px 12px; border: 1px solid #efe3b7; text-align: center; width: 80px;">الترتيب</th>
                            <th style="padding: 10px 12px; border: 1px solid #efe3b7; text-align: center; width: 80px;">الحالة</th>
                            <th style="padding: 10px 12px; border: 1px solid #efe3b7; text-align: center; width: 140px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($faqs as $faq)
                            <tr style="{{ !$faq->is_active ? 'opacity: 0.55;' : '' }}">
                                <td style="padding: 10px 12px; border: 1px solid #efe3b7; font-weight:700; color:#6b7280;">
                                    {{ $loop->iteration }}
                                </td>
                                <td style="padding: 10px 12px; border: 1px solid #efe3b7; font-weight:700; max-width: 280px;">
                                    {{ \Illuminate\Support\Str::limit($faq->question, 120) }}
                                </td>
                                <td style="padding: 10px 12px; border: 1px solid #efe3b7; color:#374151; max-width: 340px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ \Illuminate\Support\Str::limit($faq->answer, 160) }}
                                </td>
                                <td style="padding: 10px 12px; border: 1px solid #efe3b7; text-align:center; font-weight:700;">
                                    {{ $faq->sort_order }}
                                </td>
                                <td style="padding: 10px 12px; border: 1px solid #efe3b7; text-align:center;">
                                    @if ($faq->is_active)
                                        <span style="background:#ecfdf3; color:#166534; border:1px solid #bbf7d0; border-radius:20px; padding:3px 10px; font-size:13px; font-weight:700;">فعّال</span>
                                    @else
                                        <span style="background:#f3f4f6; color:#6b7280; border:1px solid #d1d5db; border-radius:20px; padding:3px 10px; font-size:13px; font-weight:700;">معطّل</span>
                                    @endif
                                </td>
                                <td style="padding: 10px 12px; border: 1px solid #efe3b7; text-align:center;">
                                    <div style="display:inline-flex; gap:8px; flex-wrap:wrap; justify-content:center;">
                                        <a
                                            href="{{ route('faqs.edit', $faq) }}"
                                            style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:6px 10px; border-radius:8px; font-weight:700; font-size:13px;"
                                        >
                                            تعديل
                                        </a>
                                        <form
                                            action="{{ route('faqs.destroy', $faq) }}"
                                            method="POST"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال والجواب؟');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                style="border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; padding:6px 10px; border-radius:8px; font-weight:700; cursor:pointer; font-family:inherit; font-size:13px;"
                                            >
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
        @endif
    </section>
@endsection
