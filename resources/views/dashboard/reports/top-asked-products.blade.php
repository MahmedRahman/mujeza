@extends('layouts.app')

@section('title', 'أكثر المنتجات سؤالاً')
@section('page_title', 'التقارير — المنتجات')

@section('content')
    @php $p = $report['period']; @endphp

    <section class="card">
        <h2 style="margin-top:0; font-weight:800;">أكثر 5 منتجات سؤالاً</h2>
        <p style="margin:0 0 18px; color:#4b5563; font-weight:500;">
            ترتيب المنتجات حسب عدد مرات ذكرها في المحادثات (آخر رسالة واتساب)، نصوص الطلبات، والشكاوى خلال الفترة.
        </p>

        @include('dashboard.reports.partials.period-filter')

        <p style="margin:0 0 20px; color:#6b7280; font-size:13px; font-weight:600;">
            آخر تحديث: {{ $report['generated_at']->format('d/m/Y H:i') }}
        </p>

        <div style="border:1px solid #efe3b7; border-radius:14px; padding:18px; background:#fff;">
            <h3 style="margin:0 0 14px; font-size:16px; font-weight:800; color:#111827;">
                {{ $p['label'] }}
                <span style="font-size:12px; font-weight:600; color:#9ca3af;">(من {{ $p['from_label'] }})</span>
            </h3>

            @if (count($p['products']) === 0)
                <p style="margin:0; color:#6b7280; font-weight:600;">لا توجد إشارات لمنتجات في هذه الفترة.</p>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width:400px;">
                        <thead>
                            <tr style="background:#f8f2de;">
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:50px;">#</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">المنتج</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">عدد المرات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($p['products'] as $product)
                                <tr>
                                    <td style="padding:10px; border:1px solid #efe3b7; font-weight:900; color:#92400e;">
                                        {{ $product['rank'] }}
                                    </td>
                                    <td style="padding:10px; border:1px solid #efe3b7; font-weight:700;">
                                        {{ $product['title'] }}
                                    </td>
                                    <td style="padding:10px; border:1px solid #efe3b7; font-weight:800; color:#1d4ed8;">
                                        {{ number_format($product['mentions']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection
