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

        @if ($products->isEmpty())
            <p style="margin-bottom: 0; color: #6b7280;">لا توجد منتجات مضافة بعد.</p>
        @else
            <div style="display: grid; gap: 12px;">
                @foreach ($products as $product)
                    <article style="border: 1px solid #efe3b7; border-radius: 12px; padding: 12px;">
                        <h3 style="margin: 0 0 6px; font-weight: 700;">{{ $product->title }}</h3>
                        <p style="margin: 0 0 8px; color: #4b5563;">
                            السعر: {{ $product->price }} ر.س
                            @if ($product->discount_price)
                                - بعد الخصم: {{ $product->discount_price }} ر.س
                            @endif
                        </p>
                        <p style="margin: 0 0 8px; color: #374151;">{{ $product->description }}</p>

                        @if ($product->benefits)
                            <p style="margin: 0 0 4px;"><strong>الفوائد:</strong> {{ implode(' - ', $product->benefits) }}</p>
                        @endif
                        @if ($product->diseases)
                            <p style="margin: 0 0 4px;"><strong>الأمراض:</strong> {{ implode(' - ', $product->diseases) }}</p>
                        @endif
                        @if ($product->usage_methods)
                            <p style="margin: 0 0 4px;"><strong>طرق الاستخدام:</strong> {{ implode(' - ', $product->usage_methods) }}</p>
                        @endif
                        @if ($product->sizes)
                            <p style="margin: 0 0 4px;"><strong>الأحجام:</strong> {{ implode(' - ', $product->sizes) }}</p>
                        @endif
                        @if ($product->promo_videos)
                            <p style="margin: 0 0 4px;"><strong>الفيديوهات:</strong> {{ implode(' - ', $product->promo_videos) }}</p>
                        @endif

                        @if ($product->cover_image)
                            <img src="{{ asset('storage/' . $product->cover_image) }}" alt="{{ $product->title }}" style="margin-top: 8px; max-width: 140px; border-radius: 8px;">
                        @endif

                        <div style="margin-top: 10px; display:flex; gap:8px; flex-wrap:wrap;">
                            <a href="{{ route('products.edit', $product) }}" style="text-decoration:none; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; padding:8px 12px; border-radius:8px; font-weight:700;">
                                تعديل المنتج
                            </a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('هل أنت متأكد من حذف المنتج؟')" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:8px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                    حذف المنتج
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
