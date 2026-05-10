@extends('layouts.app')

@section('title', 'تعديل سؤال وجواب')
@section('page_title', 'تعديل سؤال وجواب')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">تعديل سؤال وجواب</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            عدّل السؤال والجواب أدناه ثم احفظ التغييرات.
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

        <form method="POST" action="{{ route('faqs.update', $faq) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">السؤال <span style="color:#c1121f;">*</span></label>
                <textarea
                    name="question"
                    rows="3"
                    required
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;"
                >{{ old('question', $faq->question) }}</textarea>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">الجواب <span style="color:#c1121f;">*</span></label>
                <textarea
                    name="answer"
                    rows="6"
                    required
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;"
                >{{ old('answer', $faq->answer) }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:20px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">الترتيب</label>
                    <input
                        name="sort_order"
                        type="number"
                        min="0"
                        value="{{ old('sort_order', $faq->sort_order) }}"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;"
                    >
                    <small style="color:#6b7280; font-size:12px;">الرقم الأصغر يظهر أولاً</small>
                </div>

                <div style="display:flex; align-items:center; gap:10px; padding-top:24px;">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        value="1"
                        {{ old('is_active', $faq->is_active) ? 'checked' : '' }}
                        style="width:18px; height:18px; cursor:pointer; accent-color:#d4af37;"
                    >
                    <label for="is_active" style="font-weight:700; cursor:pointer;">فعّال (مرئي للعملاء)</label>
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 22px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                    حفظ التعديلات
                </button>
                <a href="{{ route('faqs.index') }}" style="display:inline-block; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 16px; border-radius:8px; font-weight:700;">
                    رجوع
                </a>
            </div>
        </form>
    </section>
@endsection
