@extends('layouts.app')

@section('title', 'عدد الطلبات وقيمتها')
@section('page_title', 'التقارير — الطلبات')

@section('content')
    @php $p = $report['period']; @endphp

    <section class="card">
        <h2 style="margin-top:0; font-weight:800;">عدد الطلبات وقيمتها الإجمالية</h2>
        <p style="margin:0 0 18px; color:#4b5563; font-weight:500;">
            إحصائيات الطلبات حسب تاريخ الإنشاء. القيمة الإجمالية = مجموع المنتجات + رسوم التوصيل. الطلبات الملغية غير مُحتسبة.
        </p>

        @include('dashboard.reports.partials.period-filter')

        <p style="margin:0 0 16px; color:#6b7280; font-size:13px; font-weight:600;">
            آخر تحديث: {{ $report['generated_at']->format('d/m/Y H:i') }}
        </p>

        <div style="max-width:360px; border:1px solid #efe3b7; border-radius:14px; padding:18px; background:linear-gradient(135deg, #fffcf2 0%, #fff7df 100%);">
            <div style="color:#6b7280; font-weight:800; font-size:14px; margin-bottom:12px;">{{ $p['label'] }}</div>
            <div style="margin-bottom:14px;">
                <div style="font-size:12px; color:#9ca3af; font-weight:700; margin-bottom:4px;">عدد الطلبات</div>
                <div style="font-size:32px; font-weight:900; color:#111827; line-height:1;">{{ number_format($p['count']) }}</div>
            </div>
            <div>
                <div style="font-size:12px; color:#9ca3af; font-weight:700; margin-bottom:4px;">القيمة الإجمالية</div>
                <div style="font-size:28px; font-weight:900; color:#15803d; line-height:1;">
                    {{ number_format($p['total'], 3) }}
                    <span style="font-size:14px; font-weight:800; color:#6b7280;">د.ك</span>
                </div>
            </div>
            <div style="font-size:12px; color:#9ca3af; font-weight:600; margin-top:12px;">من {{ $p['from_label'] }} حتى الآن</div>
        </div>
    </section>
@endsection
