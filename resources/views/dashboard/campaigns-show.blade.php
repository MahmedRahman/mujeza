@extends('layouts.app')

@section('title', 'تفاصيل الحملة: ' . $campaign->name)
@section('page_title', 'تفاصيل الحملة')

@section('content')

@php
    $isPending   = $campaign->status === 'pending';
    $isSent      = $campaign->status === 'sent';
    $isCancelled = $campaign->status === 'cancelled';

    $statusLabel = match($campaign->status) {
        'pending'   => 'في الانتظار',
        'sent'      => 'تم الإرسال',
        'cancelled' => 'ملغاة',
        default     => $campaign->status,
    };
    $statusColor = match($campaign->status) {
        'pending'   => ['bg' => '#fef9c3', 'color' => '#854d0e', 'border' => '#fde68a'],
        'sent'      => ['bg' => '#dcfce7', 'color' => '#166534', 'border' => '#bbf7d0'],
        'cancelled' => ['bg' => '#f1f5f9', 'color' => '#64748b', 'border' => '#cbd5e1'],
        default     => ['bg' => '#f3f4f6', 'color' => '#374151', 'border' => '#d1d5db'],
    };
@endphp

{{-- ─── Flash messages ─── --}}
@if (session('success'))
    <div style="background:#ecfdf3; color:#166534; border:1px solid #bbf7d0; border-radius:10px; padding:10px 14px; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div style="background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:10px; padding:10px 14px; margin-bottom:16px;">
        {{ session('error') }}
    </div>
@endif

{{-- ─── بطاقة الرأس ─── --}}
<section class="card" style="margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px;">
        <div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:6px;">
                <h2 style="margin:0; font-weight:700; font-size:20px;">{{ $campaign->name }}</h2>
                <span style="background:{{ $statusColor['bg'] }}; color:{{ $statusColor['color'] }}; border:1px solid {{ $statusColor['border'] }}; border-radius:20px; padding:3px 14px; font-weight:700; font-size:13px;">
                    {{ $statusLabel }}
                </span>
            </div>
            <div style="color:#6b7280; font-size:13px;">
                أُنشِئت في {{ $campaign->created_at?->format('Y-m-d H:i') }}
            </div>
        </div>

        {{-- ─── أزرار التحكم ─── --}}
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">

            @if ($isPending)
                {{-- تشغيل --}}
                <form method="POST" action="{{ route('campaigns.dispatch', $campaign) }}"
                      onsubmit="return confirm('هل أنت متأكد من إرسال الحملة الآن لـ {{ $campaign->phones_count }} رقم؟')">
                    @csrf
                    <button type="submit"
                            style="border:none; background:#16a34a; color:#fff; padding:10px 20px; border-radius:8px; font-weight:800; font-family:inherit; font-size:14px; cursor:pointer;">
                        ▶ تشغيل الحملة
                    </button>
                </form>
                {{-- إلغاء --}}
                <form method="POST" action="{{ route('campaigns.cancel', $campaign) }}"
                      onsubmit="return confirm('هل تريد إلغاء هذه الحملة؟')">
                    @csrf
                    <button type="submit"
                            style="border:1px solid #d1d5db; background:#fff; color:#374151; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit; font-size:14px; cursor:pointer;">
                        ✕ إلغاء الحملة
                    </button>
                </form>
            @endif

            @if ($isSent || $isCancelled)
                {{-- إعادة إرسال --}}
                <form method="POST" action="{{ route('campaigns.resend', $campaign) }}"
                      onsubmit="return confirm('هل تريد إعادة إرسال الحملة لنفس الأرقام؟')">
                    @csrf
                    <button type="submit"
                            style="border:none; background:#2563eb; color:#fff; padding:10px 20px; border-radius:8px; font-weight:800; font-family:inherit; font-size:14px; cursor:pointer;">
                        🔄 إعادة الإرسال
                    </button>
                </form>
            @endif

            <a href="{{ route('campaigns.create') }}"
               style="text-decoration:none; background:#f8f2de; color:#92400e; border:1px solid #d4af37; padding:10px 18px; border-radius:8px; font-weight:700; font-size:14px;">
                📣 حملة جديدة
            </a>
            <a href="{{ route('campaigns.index') }}"
               style="text-decoration:none; background:#f3f4f6; color:#374151; padding:10px 18px; border-radius:8px; font-weight:700; font-size:14px;">
                ← كل الحملات
            </a>
        </div>
    </div>

    {{-- ─── إحصائيات ─── --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px,1fr)); gap:12px; margin-top:20px;">
        <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2; text-align:center;">
            <div style="color:#6b7280; font-weight:700; font-size:13px; margin-bottom:4px;">إجمالي الأرقام</div>
            <div style="font-size:28px; font-weight:900; color:#111827;">{{ $campaign->phones_count }}</div>
        </div>

        @if ($isSent)
        <div style="border:1px solid #bbf7d0; border-radius:10px; padding:14px; background:#f0fdf4; text-align:center;">
            <div style="color:#166534; font-weight:700; font-size:13px; margin-bottom:4px;">نجح الإرسال</div>
            <div style="font-size:28px; font-weight:900; color:#16a34a;">{{ $campaign->success_count }}</div>
        </div>
        <div style="border:1px solid #fca5a5; border-radius:10px; padding:14px; background:#fef2f2; text-align:center;">
            <div style="color:#b91c1c; font-weight:700; font-size:13px; margin-bottom:4px;">فشل الإرسال</div>
            <div style="font-size:28px; font-weight:900; color:#b91c1c;">{{ $campaign->failed_count }}</div>
        </div>
        <div style="border:1px solid #e5e7eb; border-radius:10px; padding:14px; background:#f9fafb; text-align:center;">
            <div style="color:#6b7280; font-weight:700; font-size:13px; margin-bottom:4px;">نسبة النجاح</div>
            <div style="font-size:28px; font-weight:900; color:#111827;">
                {{ $campaign->phones_count > 0 ? round($campaign->success_count / $campaign->phones_count * 100) : 0 }}%
            </div>
        </div>
        @endif
    </div>

    {{-- ─── نص الرسالة ─── --}}
    <div style="margin-top:18px; padding:14px; background:#f8f9fa; border-radius:8px; border:1px solid #e5e7eb;">
        <div style="font-weight:700; margin-bottom:6px; color:#374151;">نص الرسالة:</div>
        <div style="white-space:pre-wrap; color:#4b5563; font-size:14px; line-height:1.6;">{{ $campaign->message }}</div>
    </div>
</section>

{{-- ─── قائمة الأرقام / نتائج الإرسال ─── --}}
<section class="card">

    @if ($isPending)
        <h3 style="margin-top:0; font-weight:700;">الأرقام المحددة للإرسال</h3>
        <p style="color:#6b7280; margin-bottom:14px; font-size:14px;">
            هذه الأرقام ستُرسَل إليها الرسالة عند تشغيل الحملة.
        </p>
    @elseif ($isSent)
        <h3 style="margin-top:0; font-weight:700;">نتائج الإرسال لكل رقم</h3>
    @else
        <h3 style="margin-top:0; font-weight:700;">الأرقام المحددة (الحملة ملغاة)</h3>
    @endif

    @php $results = $campaign->results ?? []; @endphp

    @if (empty($results))
        <p style="color:#9ca3af;">لا توجد بيانات.</p>
    @else

        @if ($isSent)
        <div style="margin-bottom:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <button onclick="filterResults('all')" id="btn-all"
                    style="border:1px solid #d4af37; background:#fffcf2; color:#92400e; padding:7px 16px; border-radius:20px; font-weight:700; font-family:inherit; cursor:pointer; font-size:13px; box-shadow:0 0 0 2px #d4af37;">
                الكل ({{ count($results) }})
            </button>
            <button onclick="filterResults('success')" id="btn-success"
                    style="border:1px solid #bbf7d0; background:#f0fdf4; color:#166534; padding:7px 16px; border-radius:20px; font-weight:700; font-family:inherit; cursor:pointer; font-size:13px;">
                ✓ نجح ({{ $campaign->success_count }})
            </button>
            <button onclick="filterResults('error')" id="btn-error"
                    style="border:1px solid #fca5a5; background:#fef2f2; color:#b91c1c; padding:7px 16px; border-radius:20px; font-weight:700; font-family:inherit; cursor:pointer; font-size:13px;">
                ✗ فشل ({{ $campaign->failed_count }})
            </button>
        </div>
        @endif

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:420px;" id="resultsTable">
                <thead>
                    <tr style="background:#f8f2de;">
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">#</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الاسم</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الرقم</th>
                        @if ($isSent)
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الحالة</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">التفاصيل</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $i => $r)
                        @php
                            $rowStatus = $r['status'] ?? null;
                            $rowBg = $isSent
                                ? ($rowStatus === 'success' ? '#f0fdf4' : '#fef2f2')
                                : '';
                        @endphp
                        <tr class="result-row" data-status="{{ $rowStatus }}"
                            style="{{ $rowBg ? 'background:'.$rowBg.';' : '' }}">
                            <td style="padding:10px; border:1px solid #efe3b7; color:#9ca3af; font-size:13px;">{{ $i + 1 }}</td>
                            <td style="padding:10px; border:1px solid #efe3b7; font-weight:600;">{{ $r['name'] }}</td>
                            <td style="padding:10px; border:1px solid #efe3b7; direction:ltr; text-align:right;">
                                <span style="font-family:monospace; background:#f1f5f9; border-radius:5px; padding:2px 7px;">{{ $r['phone'] }}</span>
                            </td>
                            @if ($isSent)
                            <td style="padding:10px; border:1px solid #efe3b7;">
                                @if ($rowStatus === 'success')
                                    <span style="background:#dcfce7; color:#166534; border-radius:20px; padding:3px 12px; font-weight:700; font-size:13px;">✓ نجح</span>
                                @else
                                    <span style="background:#fee2e2; color:#b91c1c; border-radius:20px; padding:3px 12px; font-weight:700; font-size:13px;">✗ فشل</span>
                                @endif
                            </td>
                            <td style="padding:10px; border:1px solid #efe3b7; font-size:13px; color:#4b5563;">
                                {{ $r['message'] ?? '' }}
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@if ($isSent)
<script>
function filterResults(status) {
    document.querySelectorAll('.result-row').forEach(row => {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
    ['all','success','error'].forEach(s => {
        const btn = document.getElementById('btn-' + s);
        if (!btn) return;
        btn.style.boxShadow = s === status ? '0 0 0 2px #d4af37' : 'none';
        btn.style.fontWeight = s === status ? '900' : '700';
    });
}
</script>
@endif

@endsection
