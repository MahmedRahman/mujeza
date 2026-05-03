@extends('layouts.app')

@section('title', 'الإعدادات')
@section('page_title', 'الإعدادات')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">إعدادات النظام</h2>
        <p style="margin-bottom: 18px; color: #4b5563; font-weight: 500;">
            يمكنك تعديل بيانات المتجر الأساسية من هنا.
        </p>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 700;">اسم المتجر</label>
                <input name="store_name" type="text" value="{{ old('store_name', $settings['store_name'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 700;">البريد الإلكتروني</label>
                <input name="email" type="email" value="{{ old('email', $settings['email'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
            </div>

            <h3 style="margin: 20px 0 10px; font-weight: 800;">بيانات التواصل</h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">رقم تليفون 1</label>
                    <input name="phone1" type="text" value="{{ old('phone1', $settings['phone1'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">رقم تليفون 2</label>
                    <input name="phone2" type="text" value="{{ old('phone2', $settings['phone2'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">واتساب</label>
                    <input name="whatsapp" type="text" value="{{ old('whatsapp', $settings['whatsapp'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 700;">العنوان</label>
                <input name="address" type="text" value="{{ old('address', $settings['address'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">رابط فيسبوك</label>
                    <input name="facebook" type="url" value="{{ old('facebook', $settings['facebook'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">رابط إنستجرام</label>
                    <input name="instagram" type="url" value="{{ old('instagram', $settings['instagram'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">الموقع الإلكتروني</label>
                    <input name="website" type="url" value="{{ old('website', $settings['website'] ?? '') }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
            </div>

            <h3 style="margin: 20px 0 10px; font-weight: 800;">نبذة عن الشركة</h3>
            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 700;">نبذة بسيطة</label>
                <textarea name="company_about" rows="5" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">{{ old('company_about', $settings['company_about'] ?? '') }}</textarea>
            </div>

            <h3 style="margin: 20px 0 10px; font-weight: 800;">إعدادات Chatwoot</h3>
            <p style="margin: 0 0 10px; color: #4b5563; font-weight: 500; font-size: 14px;">
                أدخل بيانات Chatwoot لعرض المحادثات داخل لوحة الإدارة.
            </p>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">رابط Chatwoot</label>
                    <input name="chatwoot_url" type="url" value="{{ old('chatwoot_url', $settings['chatwoot_url'] ?? '') }}" placeholder="مثال: https://app.chatwoot.com" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">Chatwoot User Token</label>
                    <input name="chatwoot_token" type="text" value="{{ old('chatwoot_token', $settings['chatwoot_token'] ?? '') }}" placeholder="User Access Token من إعدادات Chatwoot" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
            </div>

            <h3 style="margin: 20px 0 10px; font-weight: 800;">إعدادات Evolution API (واتساب)</h3>
            <p style="margin: 0 0 10px; color: #4b5563; font-weight: 500; font-size: 14px;">
                أدخل بيانات Evolution API لتفعيل الشات داخل لوحة الإدارة.
            </p>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">رابط Evolution API</label>
                    <input name="evo_url" type="url" value="{{ old('evo_url', $settings['evo_url'] ?? '') }}" placeholder="مثال: http://evo.premierforanimal.com" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">API Key</label>
                    <input name="evo_api_key" type="text" value="{{ old('evo_api_key', $settings['evo_api_key'] ?? '') }}" placeholder="Global API Key" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 700;">Instance Name</label>
                    <input name="evo_instance" type="text" value="{{ old('evo_instance', $settings['evo_instance'] ?? '') }}" placeholder="اسم الـ instance في Evolution" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
                </div>
            </div>

            <button type="submit" style="border: none; background: #d4af37; color: #111827; padding: 10px 16px; border-radius: 8px; font-weight: 700; font-family: inherit;">
                حفظ التعديلات
            </button>
        </form>
    </section>
@endsection
