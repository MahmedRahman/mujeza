@extends('layouts.app')

@section('title', 'الرئيسية')
@section('page_title', 'الرئيسية')

@section('content')
    <style>
        .dashboard-wrap {
            display: grid;
            gap: 16px;
        }
        .dashboard-hero {
            border: 1px solid #efe3b7;
            border-radius: 16px;
            padding: 18px;
            background: linear-gradient(135deg, #fffcf2 0%, #fff7df 100%);
            box-shadow: 0 8px 22px rgba(17, 24, 39, 0.05);
        }
        .dashboard-hero h2 {
            margin: 0 0 6px;
            font-weight: 800;
        }
        .dashboard-hero p {
            margin: 0;
            color: #4b5563;
            font-weight: 600;
        }
        .stats-section {
            border: 1px solid #efe3b7;
            border-radius: 14px;
            padding: 14px;
            background: #fff;
        }
        .stats-section h3 {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .stat-card {
            border: 1px solid #f2e8c8;
            border-radius: 12px;
            padding: 12px;
            background: #fffcf2;
        }
        .stat-label {
            color: #6b7280;
            font-weight: 800;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .stat-value {
            font-size: 28px;
            line-height: 1;
            font-weight: 900;
            color: #111827;
        }
        .value-green { color: #166534; }
        .value-red { color: #b91c1c; }
    </style>

    <div class="dashboard-wrap">
        <section class="dashboard-hero">
            <h2>نظرة عامة على الأداء</h2>
            <p>متابعة سريعة لحالة المنتجات والطلبات والشكاوى والبيانات الأساسية في النظام.</p>
        </section>

        <section class="stats-section">
            <h3>إحصائيات المنتجات</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">إجمالي المنتجات</div>
                    <div class="stat-value">{{ $stats['products'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">المنتجات المتاحة</div>
                    <div class="stat-value value-green">{{ $stats['available_products'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">المنتجات غير المتاحة</div>
                    <div class="stat-value value-red">{{ $stats['unavailable_products'] ?? 0 }}</div>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <h3>إحصائيات الطلبات</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">إجمالي الطلبات</div>
                    <div class="stat-value">{{ $stats['orders'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">طلبات اليوم</div>
                    <div class="stat-value">{{ $stats['orders_today'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">طلبات آخر 7 أيام</div>
                    <div class="stat-value">{{ $stats['orders_last7days'] ?? 0 }}</div>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <h3>إحصائيات الشكاوى والاستفسارات</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">الإجمالي</div>
                    <div class="stat-value">{{ $stats['complaints'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">شكاوى اليوم</div>
                    <div class="stat-value">{{ $stats['complaints_today'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">آخر 7 أيام</div>
                    <div class="stat-value">{{ $stats['complaints_last7days'] ?? 0 }}</div>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <h3>بيانات عامة</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">عدد الفروع</div>
                    <div class="stat-value">{{ $stats['branches'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">الفئات</div>
                    <div class="stat-value">{{ $stats['categories'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">الأمراض</div>
                    <div class="stat-value">{{ $stats['diseases'] ?? 0 }}</div>
                </div>
            </div>
        </section>
    </div>
@endsection
