@extends('layouts.app')

@section('title', 'الرئيسية')
@section('page_title', 'الرئيسية')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">نظرة عامة</h2>
        <p style="margin-bottom: 12px; color: #4b5563; font-weight: 500;">
            هذه الصفحة الرئيسية للداش بورد. من هنا تقدر تتابع أهم المعلومات بسرعة.
        </p>
        <a href="{{ route('categories.index') }}" style="display:inline-block; text-decoration:none; border:none; background:#d4af37; color:#111827; padding:10px 14px; border-radius:8px; font-weight:700;">
            إدارة الفئات
        </a>
    </section>
@endsection
