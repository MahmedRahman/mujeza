@extends('layouts.app')

@section('title', 'تعديل فرع')
@section('page_title', 'تعديل فرع')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">تعديل الفرع</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            عدل بيانات الفرع ثم احفظ التغييرات.
        </p>

        <form method="POST" action="{{ route('branches.update', $branch) }}">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 12px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">اسم الفرع</label>
                    <input name="name" type="text" value="{{ old('name', $branch->name) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">تليفون 1</label>
                    <input name="phone1" type="text" value="{{ old('phone1', $branch->phone1) }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">تليفون 2</label>
                    <input name="phone2" type="text" value="{{ old('phone2', $branch->phone2) }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 12px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">العنوان (اختياري)</label>
                    <input name="address" type="text" value="{{ old('address', $branch->address) }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Latitude (اختياري)</label>
                    <input name="latitude" type="text" value="{{ old('latitude', $branch->latitude) }}" placeholder="مثال: 29.3759" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Longitude (اختياري)</label>
                    <input name="longitude" type="text" value="{{ old('longitude', $branch->longitude) }}" placeholder="مثال: 47.9784" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">رابط الخريطة (Embed URL - اختياري)</label>
                <input name="map_url" type="text" value="{{ old('map_url', $branch->map_url) }}" placeholder="مثال: https://www.google.com/maps?...&output=embed" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
            </div>

            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                    حفظ التعديلات
                </button>
                <a href="{{ route('branches.index') }}"
                   style="display:inline-block; text-decoration:none; border:1px solid #d1d5db; color:#374151; background:#fff; padding:10px 18px; border-radius:8px; font-weight:700;">
                    رجوع
                </a>
            </div>
        </form>
    </section>
@endsection

