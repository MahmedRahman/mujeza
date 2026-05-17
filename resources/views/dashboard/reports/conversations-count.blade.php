@extends('layouts.app')

@section('title', 'عدد المحادثات')
@section('page_title', 'التقارير — عدد المحادثات')

@section('content')
    @php $p = $report['period']; @endphp

    <section class="card">
        <h2 style="margin-top:0; font-weight:800;">عدد المحادثات</h2>
        <p style="margin:0 0 18px; color:#4b5563; font-weight:500;">
            عدد المحادثات الفردية التي كان آخر نشاط فيها ضمن الفترة المحددة (حسب آخر رسالة من واتساب).
        </p>

        @include('dashboard.reports.partials.period-filter')

        @if ($report['error'])
            <div style="background:#fff1f2; color:#b91c1c; border:1px solid #fecaca; border-radius:10px; padding:12px 14px; margin-bottom:16px; font-weight:700;">
                {{ $report['error'] }}
            </div>
        @else
            <p style="margin:0 0 16px; color:#6b7280; font-size:13px; font-weight:600;">
                إجمالي المحادثات النشطة المسجّلة: <strong style="color:#111827;">{{ number_format($report['total_chats']) }}</strong>
                @if ($report['generated_at'])
                    — آخر تحديث: {{ $report['generated_at']->format('d/m/Y H:i') }}
                @endif
            </p>
        @endif

        <div style="max-width:320px; border:1px solid #efe3b7; border-radius:14px; padding:18px; background:linear-gradient(135deg, #fffcf2 0%, #fff7df 100%);">
            <div style="color:#6b7280; font-weight:800; font-size:14px; margin-bottom:8px;">{{ $p['label'] }}</div>
            <div style="font-size:36px; font-weight:900; color:#111827; line-height:1; margin-bottom:6px;">
                {{ number_format($p['count']) }}
            </div>
            <div style="font-size:12px; color:#9ca3af; font-weight:600;">من {{ $p['from_label'] }} حتى الآن</div>
        </div>

        <p style="margin:18px 0 0; color:#9ca3af; font-size:12px; font-weight:600;">
            البيانات تُحدَّث كل 5 دقائق من خادم واتساب. المحادثات الجماعية غير مُحتسبة.
        </p>
    </section>
@endsection
