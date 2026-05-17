@extends('layouts.app')

@section('title', 'الطلبات حسب الحالة')
@section('page_title', 'التقارير — الطلبات')

@section('content')
    @php
        $p = $report['period'];
        $statusStyles = [
            'طلب جديد' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'bar' => '#3b82f6'],
            'تم التأكيد' => ['bg' => '#f0fdf4', 'color' => '#15803d', 'bar' => '#22c55e'],
            'قيد التجهيز' => ['bg' => '#fffbeb', 'color' => '#92400e', 'bar' => '#f59e0b'],
            'خرج للتوصيل' => ['bg' => '#faf5ff', 'color' => '#7e22ce', 'bar' => '#a855f7'],
            'مكتمل' => ['bg' => '#ecfdf5', 'color' => '#047857', 'bar' => '#10b981'],
            'ملغي' => ['bg' => '#fff1f2', 'color' => '#b91c1c', 'bar' => '#ef4444'],
        ];
    @endphp

    <section class="card">
        <h2 style="margin-top:0; font-weight:800;">الطلبات حسب الحالة (نسب مئوية)</h2>
        <p style="margin:0 0 18px; color:#4b5563; font-weight:500;">
            توزيع الطلبات على الحالات خلال الفترة المحددة — تشمل جميع الطلبات بما فيها الملغية.
        </p>

        @include('dashboard.reports.partials.period-filter')

        <p style="margin:0 0 20px; color:#6b7280; font-size:13px; font-weight:600;">
            آخر تحديث: {{ $report['generated_at']->format('d/m/Y H:i') }}
        </p>

        <div style="border:1px solid #efe3b7; border-radius:14px; padding:18px; background:#fff;">
            <h3 style="margin:0 0 6px; font-size:16px; font-weight:800; color:#111827;">
                {{ $p['label'] }}
                <span style="font-size:12px; font-weight:600; color:#9ca3af;">(من {{ $p['from_label'] }})</span>
            </h3>
            <p style="margin:0 0 14px; font-size:13px; color:#6b7280; font-weight:600;">
                إجمالي الطلبات: <strong style="color:#111827;">{{ number_format($p['total']) }}</strong>
            </p>

            @if ($p['total'] === 0)
                <p style="margin:0; color:#6b7280; font-weight:600;">لا توجد طلبات في هذه الفترة.</p>
            @else
                <div style="display:grid; gap:10px;">
                    @foreach ($p['statuses'] as $row)
                        @php
                            $style = $statusStyles[$row['status']] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'bar' => '#9ca3af'];
                        @endphp
                        <div style="border:1px solid #efe3b7; border-radius:10px; padding:12px 14px; background:{{ $style['bg'] }}; opacity:{{ $row['count'] > 0 ? '1' : '0.55' }};">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:8px; flex-wrap:wrap;">
                                <span style="font-weight:800; color:{{ $style['color'] }};">{{ $row['status'] }}</span>
                                <span style="font-weight:800; color:#111827;">
                                    {{ number_format($row['count']) }}
                                    <span style="color:{{ $style['color'] }};">({{ number_format($row['percent'], 1) }}%)</span>
                                </span>
                            </div>
                            <div style="height:8px; background:rgba(0,0,0,0.08); border-radius:999px; overflow:hidden;">
                                <div style="height:100%; width:{{ min($row['percent'], 100) }}%; background:{{ $style['bar'] }}; border-radius:999px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
