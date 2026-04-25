@extends('layouts.app')

@section('title', 'تعديل منتج')
@section('page_title', 'تعديل منتج')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">تعديل منتج</h2>
        <p style="margin-bottom: 18px; color: #4b5563; font-weight: 500;">
            نفس صفحة الإضافة مع تعبئة بيانات المنتج الحالية للتعديل.
        </p>
        <a href="{{ route('products.index') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:8px 14px; border-radius:8px; font-weight:700;">
            رجوع إلى قائمة المنتجات
        </a>

        @if ($errors->any())
            <div style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 12px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">عنوان المنتج</label>
                    <input name="title" type="text" value="{{ old('title', $product->title) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">السعر</label>
                    <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">السعر بعد الخصم</label>
                    <input name="discount_price" type="number" step="0.01" min="0" value="{{ old('discount_price', $product->discount_price) }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
            </div>

            <div style="margin-top:12px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">وصف المنتج</label>
                <textarea name="description" rows="4" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('description', $product->description) }}</textarea>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">الأحجام (كل سطر حجم)</label>
                <textarea name="sizes[]" rows="4" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('sizes_text', implode("\n", $product->sizes ?? [])) }}</textarea>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">الفوائد (كل سطر فائدة)</label>
                <textarea name="benefits[]" rows="4" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('benefits_text', implode("\n", $product->benefits ?? [])) }}</textarea>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">الأمراض (كل سطر مرض)</label>
                <textarea name="diseases[]" rows="4" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('diseases_text', implode("\n", $product->diseases ?? [])) }}</textarea>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">طرق الاستخدام (كل سطر طريقة)</label>
                <textarea name="usage_methods[]" rows="4" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('usage_methods_text', implode("\n", $product->usage_methods ?? [])) }}</textarea>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">روابط الفيديوهات (كل سطر رابط)</label>
                <textarea name="promo_videos[]" rows="3" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('promo_videos_text', implode("\n", $product->promo_videos ?? [])) }}</textarea>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">صور المنتج الجديدة (اختياري)</label>
                <input name="product_images[]" type="file" accept="image/*" multiple style="width:100%;">
                <label style="display:block; margin:8px 0 6px; font-weight:700;">رقم الصورة الرئيسية من الصور الجديدة</label>
                <input name="primary_image_index" type="number" min="0" value="{{ old('primary_image_index', 0) }}" style="width:120px; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                @if ($product->cover_image)
                    <p style="margin:10px 0 6px; color:#6b7280; font-size:14px;">الصورة الحالية:</p>
                    <img src="{{ asset('storage/' . $product->cover_image) }}" alt="{{ $product->title }}" style="max-width:140px; border-radius:8px;">
                @endif
            </div>

            <button type="submit" style="margin-top:16px; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                حفظ التعديلات
            </button>
        </form>
    </section>
@endsection
