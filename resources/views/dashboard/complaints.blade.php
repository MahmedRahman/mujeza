@extends('layouts.app')

@section('title', 'الشكاوى')
@section('page_title', 'الشكاوى')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">الشكاوى</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            راجع كل الشكاوى المضافة.
        </p>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('complaints.create') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
            + إضافة شكوى
        </a>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin: 4px 0 14px;">
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">إجمالي الشكاوى</div>
                <div style="font-size:26px; font-weight:900; color:#111827;">{{ $complaintsStats['total'] ?? 0 }}</div>
            </div>
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">شكاوى اليوم</div>
                <div style="font-size:26px; font-weight:900; color:#111827;">{{ $complaintsStats['today'] ?? 0 }}</div>
            </div>
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">آخر 7 أيام</div>
                <div style="font-size:26px; font-weight:900; color:#111827;">{{ $complaintsStats['last7days'] ?? 0 }}</div>
            </div>
        </div>

        @if ($complaints->isEmpty())
            <p style="margin:0; color:#6b7280;">لا توجد شكاوى بعد.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right; white-space:nowrap;">تاريخ الإدخال</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">remoteJid</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الاسم</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">التليفون</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">العنوان</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الوصف</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($complaints as $complaint)
                            <tr>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700; white-space:nowrap; color:#6b7280; font-size:13px;">
                                    {{ $complaint->created_at?->format('d/m/Y') ?? '—' }}
                                    <div style="font-size:11px; color:#9ca3af;">{{ $complaint->created_at?->diffForHumans() ?? '' }}</div>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-size:13px; direction:ltr; text-align:right;">
                                    <span style="font-family:monospace; color:#4b5563;">{{ $complaint->remote_jid ?? '—' }}</span>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:600; white-space:nowrap;">
                                    @if ($complaint->customer)
                                        <a href="{{ route('customers.edit', $complaint->customer->remote_jid) }}"
                                           style="text-decoration:none; color:#92400e;">
                                            {{ $complaint->customer->name }}
                                        </a>
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; white-space:nowrap;">
                                    @if ($complaint->customer?->phone)
                                        <span style="background:#f1f5f9; border-radius:6px; padding:3px 8px; font-family:monospace; font-size:13px; direction:ltr; display:inline-block;">
                                            {{ $complaint->customer->phone }}
                                        </span>
                                    @else
                                        <span style="color:#9ca3af;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700;">
                                    {{ $complaint->title }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#374151; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ \Illuminate\Support\Str::limit($complaint->description, 160) }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; text-align:center;">
                                    <div style="display:inline-flex; gap:8px; flex-wrap:wrap; justify-content:center;">
                                        <a href="{{ route('complaints.edit', $complaint) }}"
                                           style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:6px 10px; border-radius:8px; font-weight:700;">
                                            تعديل
                                        </a>
                                        <form action="{{ route('complaints.destroy', $complaint) }}" method="POST"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذه الشكوى؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; padding:6px 10px; border-radius:8px; font-weight:700; cursor:pointer; font-family:inherit;">
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
