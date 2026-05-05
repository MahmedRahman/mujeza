@extends('layouts.app')

@section('title', 'المنتجات')
@section('page_title', 'المنتجات')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">المنتجات المضافة</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            من هنا تقدر تراجع كل المنتجات، ولإضافة منتج جديد ادخل على صفحة الإضافة.
        </p>
        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif
        <a href="{{ route('products.create') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700;">
            + إضافة منتج جديد
        </a>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin: 16px 0;">
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">إجمالي المنتجات</div>
                <div style="font-size:26px; font-weight:900; color:#111827;">{{ $totalProductsCount }}</div>
            </div>
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">عدد المنتجات المتاحة</div>
                <div style="font-size:26px; font-weight:900; color:#111827;">{{ $availableProductsCount }}</div>
            </div>
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">عدد المنتجات غير المتاحة</div>
                <div style="font-size:26px; font-weight:900; color:#b91c1c;">{{ $unavailableProductsCount ?? 0 }}</div>
            </div>
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2;">
                <div style="color:#6b7280; font-weight:800; margin-bottom:6px;">نتائج الفلترة الحالية</div>
                <div style="font-size:26px; font-weight:900; color:#111827;">{{ $filteredProductsCount ?? 0 }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('products.index') }}" style="margin-bottom: 14px; display:flex; gap: 10px; flex-wrap:wrap; align-items:center;">
            <input
                name="q"
                type="text"
                value="{{ $q ?? '' }}"
                placeholder="فلتر باسم المنتج"
                style="width: 320px; max-width: 100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;"
            >
            <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:800; font-family:inherit; cursor:pointer;">
                تطبيق
            </button>
            @if (($q ?? '') !== '')
                <a href="{{ route('products.index') }}" style="display:inline-block; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 14px; border-radius:8px; font-weight:800;">
                    إلغاء الفلتر
                </a>
            @endif
        </form>

        @if ($products->isEmpty())
            <p style="margin-bottom: 0; color: #6b7280;">لا توجد منتجات مضافة بعد.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 860px;">
                    <thead>
                        <tr style="background: #f8f2de;">
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">المنتج</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">السعر</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">سعر الخصم</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الوصف</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">معلومات إضافية</th>
                            <th style="padding: 10px; border: 1px solid #efe3b7; text-align: right;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td style="padding: 10px; border: 1px solid #efe3b7; font-weight: 700;">
                                    {{ $product->title }}
                                    <div style="margin-top:6px; font-size:12px; font-weight:900; color: {{ $product->is_available ? '#166534' : '#b91c1c' }};">
                                        {{ $product->is_available ? 'متاح' : 'غير متاح' }}
                                    </div>
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">{{ $product->price }} د.ك</td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    {{ $product->discount_price ? $product->discount_price . ' د.ك' : '—' }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color: #374151; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ \Illuminate\Support\Str::limit($product->description, 140) }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7; color: #4b5563; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    @php
                                        $meta = [];

                                        if ($product->benefits) {
                                            $meta[] = 'الفوائد: ' . implode(' - ', $product->benefits);
                                        }

                                        if ($product->diseases) {
                                            $meta[] = 'الأمراض: ' . implode(' - ', $product->diseases);
                                        }

                                        if ($product->usage_methods) {
                                            $meta[] = 'طرق الاستخدام: ' . implode(' - ', $product->usage_methods);
                                        }

                                        if ($product->sizes) {
                                            $meta[] = 'الأحجام: ' . implode(' - ', $product->sizes);
                                        }
                                    @endphp

                                    {{ !empty($meta) ? implode(' | ', $meta) : '—' }}
                                </td>
                                <td style="padding: 10px; border: 1px solid #efe3b7;">
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('products.edit', $product) }}" style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:7px 10px; border-radius:8px; font-weight:700;">
                                            تعديل
                                        </a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('هل أنت متأكد من حذف المنتج؟')" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:7px 10px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
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
            <div style="margin-top:14px;">
                {{ $products->links() }}
            </div>
        @endif
    </section>

@endsection
