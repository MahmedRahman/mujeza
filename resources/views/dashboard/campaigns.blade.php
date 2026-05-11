@extends('layouts.app')

@section('title', 'الحملات الإعلانية')
@section('page_title', 'الحملات الإعلانية')

@section('content')
<section class="card">
    <h2 style="margin-top:0; font-weight:700;">الحملات الإعلانية</h2>
    <p style="color:#4b5563; margin-bottom:18px; font-weight:500;">
        كل الحملات الإعلانية المحفوظة. يمكنك إنشاء حملة جديدة أو تشغيل حملة منتظرة أو مراجعة نتائج حملة سابقة.
    </p>

    @if (session('success'))
        <div style="background:#ecfdf3; color:#166534; border:1px solid #bbf7d0; border-radius:10px; padding:10px 12px; margin-bottom:14px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:20px;">
        <a href="{{ route('campaigns.create') }}"
           style="display:inline-block; text-decoration:none; background:#16a34a; color:#fff; padding:11px 22px; border-radius:8px; font-weight:700; font-size:15px;">
            📣 إنشاء حملة جديدة
        </a>
    </div>

    @if ($campaigns->isEmpty())
        <div style="text-align:center; padding:40px; color:#9ca3af;">
            <div style="font-size:40px; margin-bottom:10px;">📭</div>
            <div style="font-size:16px; font-weight:600;">لا توجد حملات بعد</div>
            <div style="margin-top:6px; font-size:14px;">اضغط على "إنشاء حملة جديدة" للبداية</div>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:640px;">
                <thead>
                    <tr style="background:#f8f2de;">
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">#</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">اسم الحملة</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الحالة</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:center;">الأرقام</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:center;">نجح</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:center;">فشل</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">التاريخ</th>
                        <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $campaign)
                        @php
                            [$statusLabel, $statusStyle] = match($campaign->status) {
                                'pending'   => ['في الانتظار', 'background:#fef9c3;color:#854d0e;border:1px solid #fde68a;'],
                                'sent'      => ['تم الإرسال',  'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;'],
                                'cancelled' => ['ملغاة',       'background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;'],
                                default     => [$campaign->status, 'background:#f3f4f6;color:#374151;border:1px solid #d1d5db;'],
                            };
                        @endphp
                        <tr>
                            <td style="padding:10px; border:1px solid #efe3b7; color:#9ca3af; font-size:13px;">{{ $campaign->id }}</td>
                            <td style="padding:10px; border:1px solid #efe3b7; font-weight:700;">
                                <a href="{{ route('campaigns.show', $campaign) }}" style="text-decoration:none; color:#111827;">
                                    {{ $campaign->name }}
                                </a>
                            </td>
                            <td style="padding:10px; border:1px solid #efe3b7;">
                                <span style="{{ $statusStyle }} border-radius:20px; padding:3px 12px; font-weight:700; font-size:12px;">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td style="padding:10px; border:1px solid #efe3b7; font-weight:700; text-align:center;">
                                {{ $campaign->phones_count }}
                            </td>
                            <td style="padding:10px; border:1px solid #efe3b7; text-align:center;">
                                @if ($campaign->status === 'sent')
                                    <span style="background:#dcfce7; color:#166534; border-radius:20px; padding:3px 10px; font-weight:700; font-size:13px;">
                                        {{ $campaign->success_count }}
                                    </span>
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px; border:1px solid #efe3b7; text-align:center;">
                                @if ($campaign->status === 'sent' && $campaign->failed_count > 0)
                                    <span style="background:#fee2e2; color:#b91c1c; border-radius:20px; padding:3px 10px; font-weight:700; font-size:13px;">
                                        {{ $campaign->failed_count }}
                                    </span>
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px; border:1px solid #efe3b7; color:#6b7280; font-size:13px; white-space:nowrap;">
                                {{ $campaign->created_at?->format('Y-m-d H:i') }}
                            </td>
                            <td style="padding:10px; border:1px solid #efe3b7;">
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <a href="{{ route('campaigns.show', $campaign) }}"
                                       style="text-decoration:none; background:#f8f2de; color:#92400e; border:1px solid #d4af37; padding:6px 12px; border-radius:6px; font-weight:700; font-size:13px; white-space:nowrap;">
                                        @if ($campaign->status === 'pending') ▶ تشغيل @else التفاصيل @endif
                                    </a>
                                    <form method="POST" action="{{ route('campaigns.destroy', $campaign) }}"
                                          onsubmit="return confirm('هل تريد حذف هذه الحملة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="border:1px solid #fca5a5; background:#fef2f2; color:#b91c1c; padding:6px 12px; border-radius:6px; font-weight:700; font-size:13px; font-family:inherit; cursor:pointer;">
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

        <div style="margin-top:16px;">
            {{ $campaigns->links() }}
        </div>
    @endif
</section>
@endsection
