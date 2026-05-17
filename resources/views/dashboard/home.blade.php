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
        .alerts-section {
            border: 1px solid #efe3b7;
            border-radius: 14px;
            padding: 14px;
            background: #fff;
        }
        .alerts-section h3 {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }
        .alerts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 12px;
        }
        .alert-card {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }
        .alert-card.red { border-color: #fecaca; background: #fff1f2; }
        .alert-card.yellow { border-color: #fcd34d; background: #fffbeb; }
        .alert-card.blue { border-color: #bfdbfe; background: #eff6ff; }
        .alert-card.gray { border-color: #e5e7eb; background: #f9fafb; }
        .alert-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }
        .alert-title {
            font-weight: 800;
            font-size: 14px;
            color: #111827;
        }
        .alert-count {
            min-width: 28px;
            height: 28px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 13px;
            background: rgba(17, 24, 39, .08);
            color: #111827;
        }
        .alert-list {
            display: grid;
            gap: 8px;
        }
        .alert-item {
            display: block;
            text-decoration: none;
            border: 1px solid rgba(17, 24, 39, .08);
            border-radius: 8px;
            padding: 8px 10px;
            background: rgba(255, 255, 255, .7);
            color: #111827;
        }
        .alert-item:hover {
            border-color: #d4af37;
            background: #fff;
        }
        .alert-item-title {
            font-weight: 800;
            font-size: 13px;
            margin-bottom: 3px;
        }
        .alert-item-meta {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }
        .alert-empty {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            margin: 0;
        }
        .alerts-all-clear {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #15803d;
            border-radius: 10px;
            padding: 12px 14px;
            font-weight: 700;
            margin-bottom: 0;
        }
        .conversion-section {
            border: 2px solid #d4af37;
            border-radius: 16px;
            padding: 20px;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 45%, #fff7df 100%);
            box-shadow: 0 10px 28px rgba(212, 175, 55, 0.18);
        }
        .conversion-section h3 {
            margin: 0 0 4px;
            font-size: 15px;
            font-weight: 800;
            color: #92400e;
        }
        .conversion-section .conversion-subtitle {
            margin: 0 0 16px;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
        }
        .conversion-main {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .conversion-rate {
            font-size: 52px;
            line-height: 1;
            font-weight: 900;
            color: #111827;
        }
        .conversion-rate-suffix {
            font-size: 28px;
            font-weight: 900;
            color: #15803d;
            margin-bottom: 6px;
        }
        .conversion-formula {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            font-size: 14px;
            font-weight: 700;
            color: #374151;
        }
        .conversion-pill {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid #efe3b7;
            border-radius: 10px;
            padding: 8px 12px;
        }
        .conversion-pill strong {
            color: #111827;
        }
        .conversion-error {
            background: #fff1f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 12px;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 12px;
        }
        .conversion-meta {
            margin-top: 12px;
            font-size: 12px;
            color: #9ca3af;
            font-weight: 600;
        }
    </style>

    <div class="dashboard-wrap">
        <section class="dashboard-hero">
            <h2>نظرة عامة على الأداء</h2>
            <p>متابعة سريعة لحالة المنتجات والطلبات والشكاوى والبيانات الأساسية في النظام.</p>
        </section>

        <section class="conversion-section">
            <h3>نسبة تحويل المحادثة إلى طلب</h3>
            <p class="conversion-subtitle">
                {{ $conversion['period']['label'] }} (من {{ $conversion['period']['from_label'] }}) — الطلبات غير الملغية ÷ المحادثات الفردية النشطة
            </p>

            @if ($conversion['error'])
                <div class="conversion-error">{{ $conversion['error'] }}</div>
            @endif

            <div class="conversion-main">
                @if ($conversion['conversion_rate'] !== null)
                    <span class="conversion-rate">{{ number_format($conversion['conversion_rate'], 1) }}</span>
                    <span class="conversion-rate-suffix">%</span>
                @else
                    <span class="conversion-rate" style="font-size:32px; color:#6b7280;">—</span>
                @endif
            </div>

            <div class="conversion-formula">
                <span class="conversion-pill"><strong>{{ number_format($conversion['orders_count']) }}</strong> طلب</span>
                <span>÷</span>
                <span class="conversion-pill"><strong>{{ number_format($conversion['conversations_count']) }}</strong> محادثة</span>
                <span>× 100</span>
            </div>

            @if ($conversion['generated_at'])
                <p class="conversion-meta">
                    آخر تحديث للمحادثات: {{ $conversion['generated_at']->format('d/m/Y H:i') }}
                </p>
            @endif
        </section>

        <section class="alerts-section">
            <h3>تنبيهات تحتاج انتباهاً فورياً</h3>

            @if (! $hasAlerts)
                <p class="alerts-all-clear">لا توجد تنبيهات عاجلة حالياً — كل شيء تحت السيطرة.</p>
            @else
                <div class="alerts-grid">
                    <article class="alert-card red">
                        <div class="alert-head">
                            <span class="alert-title">🔴 طلبات جديدة لم تُؤكد بعد</span>
                            <span class="alert-count">{{ $alerts['unconfirmed_orders']->count() }}</span>
                        </div>
                        @if ($alerts['unconfirmed_orders']->isEmpty())
                            <p class="alert-empty">لا توجد طلبات بانتظار التأكيد.</p>
                        @else
                            <div class="alert-list">
                                @foreach ($alerts['unconfirmed_orders'] as $order)
                                    <a class="alert-item" href="{{ route('orders.show', $order) }}">
                                        <div class="alert-item-title">طلب #{{ $order->order_number }}</div>
                                        <div class="alert-item-meta">
                                            {{ $order->customer_name ?: ($order->phone ?: 'عميل غير محدد') }}
                                            · {{ $order->created_at?->diffForHumans() }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>

                    <article class="alert-card yellow">
                        <div class="alert-head">
                            <span class="alert-title">🟡 شكاوى جديدة تحتاج معالجة</span>
                            <span class="alert-count">{{ $alerts['new_complaints']->count() }}</span>
                        </div>
                        @if ($alerts['new_complaints']->isEmpty())
                            <p class="alert-empty">لا توجد شكاوى جديدة.</p>
                        @else
                            <div class="alert-list">
                                @foreach ($alerts['new_complaints'] as $complaint)
                                    <a class="alert-item" href="{{ route('complaints.edit', $complaint) }}">
                                        <div class="alert-item-title">{{ $complaint->title }}</div>
                                        <div class="alert-item-meta">{{ $complaint->created_at?->diffForHumans() }}</div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>

                    <article class="alert-card blue">
                        <div class="alert-head">
                            <span class="alert-title">🔵 محادثات تحتاج تدخل بشري</span>
                            <span class="alert-count">{{ $alerts['human_chats']['total'] ?? 0 }}</span>
                        </div>
                        @if (! empty($alerts['human_chats']['error']))
                            <p class="alert-empty">{{ $alerts['human_chats']['error'] }}</p>
                        @elseif (empty($alerts['human_chats']['items']))
                            <p class="alert-empty">لا توجد محادثات بانتظار رد يدوي.</p>
                        @else
                            <div class="alert-list">
                                @foreach ($alerts['human_chats']['items'] as $chat)
                                    <a class="alert-item" href="{{ route('conversations.index') }}">
                                        <div class="alert-item-title">{{ $chat['name'] ?: $chat['phone'] }}</div>
                                        <div class="alert-item-meta">
                                            {{ $chat['unread'] }} رسالة غير مقروءة
                                            @if (! empty($chat['last_message']))
                                                · {{ \Illuminate\Support\Str::limit($chat['last_message'], 40) }}
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>

                    <article class="alert-card gray">
                        <div class="alert-head">
                            <span class="alert-title">⚪ منتجات يتكرر السؤال عنها</span>
                            <span class="alert-count">{{ $alerts['frequent_products']->count() }}</span>
                        </div>
                        @if ($alerts['frequent_products']->isEmpty())
                            <p class="alert-empty">لا توجد منتجات متكررة السؤال عنها مؤخراً.</p>
                        @else
                            <div class="alert-list">
                                @foreach ($alerts['frequent_products'] as $row)
                                    <a class="alert-item" href="{{ route('products.edit', $row['product']) }}">
                                        <div class="alert-item-title">{{ $row['product']->title }}</div>
                                        <div class="alert-item-meta">
                                            ذُكر {{ $row['mentions'] }} مرة خلال 30 يوماً
                                            @if ($row['needs_ai_update'])
                                                · قد يحتاج تحديث بيانات AI
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>
                </div>
            @endif
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
