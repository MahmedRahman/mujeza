@extends('layouts.app')

@section('title', 'الشكاوى والاستفسارات')
@section('page_title', 'الشكاوى والاستفسارات')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">الشكاوى والاستفسارات</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            راجع كل الشكاوى والاستفسارات المضافة.
        </p>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('complaints.create') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
            + إضافة شكوى / استفسار
        </a>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin: 4px 0 14px;">
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">إجمالي الشكاوى والاستفسارات</div>
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
            <p style="margin:0; color:#6b7280;">لا توجد شكاوى/استفسارات بعد.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 820px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">العنوان</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الوصف</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">رقم التليفون</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">تاريخ الإدخال</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($complaints as $complaint)
                            <tr>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700;">
                                    {{ $complaint->title }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color:#374151; max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ \Illuminate\Support\Str::limit($complaint->description, 180) }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    <a href="tel:{{ $complaint->phone }}" style="color:#1d4ed8; text-decoration:none; font-weight:700;">
                                        {{ $complaint->phone }}
                                    </a>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight:700;">
                                    {{ $complaint->created_at?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; text-align:center;">
                                    <div style="display:inline-flex; gap:8px; flex-wrap:wrap; justify-content:center;">
                                        <a
                                            href="{{ route('complaints.edit', $complaint) }}"
                                            style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:6px 10px; border-radius:8px; font-weight:700;"
                                        >
                                            تعديل
                                        </a>
                                        <form
                                            action="{{ route('complaints.destroy', $complaint) }}"
                                            method="POST"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذه الشكوى/الاستفسار؟');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                style="border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; padding:6px 10px; border-radius:8px; font-weight:700; cursor:pointer;"
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

