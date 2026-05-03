@extends('layouts.app')

@section('title', 'الفروع')
@section('page_title', 'الفروع')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">الفروع</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            من هنا تقدر تراجع كل الفروع.
        </p>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('branches.create') }}"
           style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
            + إضافة فرع جديد
        </a>

        @if ($branches->isEmpty())
            <p style="margin:0; color:#6b7280;">لا توجد فروع مسجلة.</p>
        @else
            <div style="display:grid; gap: 14px;">
                @foreach ($branches as $branch)
                    @php
                        $mapSrc = null;
                        if ($branch->map_url) {
                            $mapSrc = $branch->map_url;
                        } elseif ($branch->latitude !== null && $branch->longitude !== null) {
                            $mapSrc = 'https://www.google.com/maps?q=' . $branch->latitude . ',' . $branch->longitude . '&z=15&output=embed';
                        }
                    @endphp

                    <div style="border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                        <div style="display:flex; justify-content:space-between; gap: 12px; align-items:flex-start; flex-wrap:wrap;">
                            <div style="min-width: 260px; flex: 1;">
                                <h3 style="margin:0 0 8px; font-weight: 800;">{{ $branch->name }}</h3>

                                @if ($branch->phone1 || $branch->phone2)
                                    <div style="margin-bottom: 6px;">
                                        @if ($branch->phone1)
                                            <div style="color:#4b5563; font-weight:700;">
                                                تليفون 1:
                                                <a href="tel:{{ $branch->phone1 }}" style="color:#1d4ed8; text-decoration:none;">{{ $branch->phone1 }}</a>
                                            </div>
                                        @endif

                                        @if ($branch->phone2)
                                            <div style="color:#4b5563; font-weight:700;">
                                                تليفون 2:
                                                <a href="tel:{{ $branch->phone2 }}" style="color:#1d4ed8; text-decoration:none;">{{ $branch->phone2 }}</a>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if ($branch->address)
                                    <div style="color:#4b5563; margin-bottom: 8px;">
                                        {{ $branch->address }}
                                    </div>
                                @endif

                                <div style="display:flex; gap:8px; align-items:center; margin-top:6px; flex-wrap:wrap;">
                                    @if ($mapSrc)
                                        @if ($branch->latitude !== null && $branch->longitude !== null)
                                            <a
                                                href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}"
                                                target="_blank"
                                                style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:7px 10px; border-radius:8px; font-weight:700;"
                                            >
                                                فتح على خرائط Google
                                            </a>
                                        @elseif ($branch->address)
                                            <a
                                                href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($branch->address) }}"
                                                target="_blank"
                                                style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:7px 10px; border-radius:8px; font-weight:700;"
                                            >
                                                فتح على خرائط Google
                                            </a>
                                        @endif
                                    @endif

                                    <a
                                        href="{{ route('branches.edit', $branch) }}"
                                        style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:6px 10px; border-radius:8px; font-weight:700;"
                                    >
                                        تعديل
                                    </a>
                                    <form
                                        action="{{ route('branches.destroy', $branch) }}"
                                        method="POST"
                                        onsubmit="return confirm('هل أنت متأكد من حذف هذا الفرع؟');"
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
                            </div>

                            <div style="flex: 1; min-width: 260px;">
                                @if ($mapSrc)
                                    <iframe
                                        src="{{ $mapSrc }}"
                                        style="width:100%; height: 240px; border:0; border-radius:10px;"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        allowfullscreen
                                    ></iframe>
                                @else
                                    <div style="color:#6b7280; font-weight:700;">— لا يوجد موقع</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection

