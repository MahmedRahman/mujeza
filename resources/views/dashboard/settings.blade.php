@extends('layouts.app')

@section('title', 'الإعدادات')
@section('page_title', 'الإعدادات')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">إعدادات النظام</h2>
        <p style="margin-bottom: 18px; color: #4b5563; font-weight: 500;">
            يمكنك تعديل بيانات المتجر الأساسية من هنا.
        </p>

        <form>
            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 700;">اسم المتجر</label>
                <input type="text" value="Mujeza" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 700;">البريد الإلكتروني</label>
                <input type="email" value="admin@mujeza.local" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: inherit;">
            </div>

            <button type="button" style="border: none; background: #d4af37; color: #111827; padding: 10px 16px; border-radius: 8px; font-weight: 700; font-family: inherit;">
                حفظ التعديلات
            </button>
        </form>
    </section>
@endsection
