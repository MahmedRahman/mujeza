@extends('layouts.app')

@section('title', 'تفاصيل الطلب #' . $order->order_number)
@section('page_title', 'تفاصيل الطلب')

@section('content')
    @php
        $currentStep = $order->trackingStepIndex();
        $isCancelled = $order->status === 'ملغي';
        $subtotal = $order->itemsSubtotal();
        $deliveryFee = (float) $order->delivery_fee;
        $grandTotal = $order->grandTotal();
    @endphp

    @if (session('success'))
        <div style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; border-radius:10px; padding:10px 14px; margin-bottom:14px; font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    <section class="card" style="margin-bottom:14px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
            <div>
                <h2 style="margin:0; font-weight:800;">طلب رقم {{ $order->order_number }}</h2>
                <p style="margin:6px 0 0; color:#6b7280; font-weight:600;">
                    تاريخ الإنشاء: {{ $order->created_at?->format('d/m/Y h:i A') ?? '—' }}
                </p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('orders.index') }}" style="text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:8px 14px; border-radius:8px; font-weight:700;">
                    رجوع للقائمة
                </a>
                <a href="{{ route('orders.edit', $order) }}" style="text-decoration:none; border:1px solid #fcd34d; background:#fffbeb; color:#92400e; padding:8px 14px; border-radius:8px; font-weight:700;">
                    تعديل
                </a>
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" style="text-decoration:none; border:none; background:#d4af37; color:#111827; padding:8px 14px; border-radius:8px; font-weight:800;">
                    طباعة / فاتورة
                </a>
            </div>
        </div>

        {{-- مسار التتبع البصري --}}
        <div style="border:1px solid #efe3b7; border-radius:14px; padding:20px 16px; background:linear-gradient(180deg, #fffcf2 0%, #fff 100%); margin-bottom:18px;">
            <div style="font-weight:800; margin-bottom:16px; font-size:15px;">تتبع حالة الطلب</div>

            @if ($isCancelled)
                <div style="text-align:center; padding:12px; border-radius:10px; background:#fff1f2; border:1px solid #fecaca; color:#b91c1c; font-weight:800; margin-bottom:14px;">
                    تم إلغاء هذا الطلب
                </div>
            @endif

            <div class="order-tracker" style="display:flex; align-items:flex-start; justify-content:space-between; gap:4px; overflow-x:auto; padding-bottom:6px;">
                @foreach (\App\Models\Order::TRACKING_STATUSES as $index => $stepStatus)
                    @php
                        $isDone = ! $isCancelled && $currentStep >= $index;
                        $isCurrent = ! $isCancelled && $currentStep === $index;
                        $dotBg = $isCurrent ? '#d4af37' : ($isDone ? '#15803d' : '#e5e7eb');
                        $dotColor = $isCurrent || $isDone ? '#fff' : '#9ca3af';
                        $labelColor = $isCurrent ? '#92400e' : ($isDone ? '#15803d' : '#9ca3af');
                        $lineColor = $isDone && $index < count(\App\Models\Order::TRACKING_STATUSES) - 1 ? '#15803d' : '#e5e7eb';
                    @endphp
                    <div style="flex:1; min-width:88px; text-align:center; position:relative;">
                        @if ($index < count(\App\Models\Order::TRACKING_STATUSES) - 1)
                            <div style="position:absolute; top:18px; right:calc(50% + 18px); left:calc(-50% + 18px); height:3px; background:{{ $lineColor }}; z-index:0;"></div>
                        @endif
                        <div style="width:36px; height:36px; border-radius:50%; margin:0 auto 8px; background:{{ $dotBg }}; color:{{ $dotColor }}; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; position:relative; z-index:1; box-shadow:{{ $isCurrent ? '0 0 0 4px rgba(212,175,55,.25)' : 'none' }};">
                            @if ($isDone && ! $isCurrent)
                                ✓
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <div style="font-size:11px; font-weight:{{ $isCurrent ? '800' : '700' }}; color:{{ $labelColor }}; line-height:1.35;">
                            {{ $stepStatus }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:16px; padding:12px 14px; border-radius:10px; background:#fff; border:1px solid #efe3b7; display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between;">
                <div>
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">الحالة الحالية</div>
                    @php
                        $statusStyle = match ($order->status) {
                            'طلب جديد' => 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;',
                            'تم التأكيد' => 'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;',
                            'قيد التجهيز' => 'background:#fffbeb; color:#92400e; border:1px solid #fcd34d;',
                            'خرج للتوصيل' => 'background:#faf5ff; color:#7e22ce; border:1px solid #e9d5ff;',
                            'مكتمل' => 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;',
                            'ملغي' => 'background:#fff1f2; color:#b91c1c; border:1px solid #fecaca;',
                            default => 'background:#f3f4f6; color:#374151; border:1px solid #e5e7eb;',
                        };
                    @endphp
                    <span style="display:inline-block; padding:6px 14px; border-radius:20px; font-size:14px; font-weight:800; {{ $statusStyle }}">
                        {{ $order->status }}
                    </span>
                </div>
                <div style="text-align:left;">
                    <div style="font-size:12px; color:#6b7280; font-weight:600; margin-bottom:4px;">آخر تحديث للحالة</div>
                    <div style="font-weight:800; color:#111827;">
                        {{ $order->status_changed_at?->format('d/m/Y h:i A') ?? $order->updated_at?->format('d/m/Y h:i A') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
            {{-- بيانات العميل --}}
            <div style="border:1px solid #efe3b7; border-radius:12px; padding:14px; background:#fffcf2;">
                <h3 style="margin:0 0 12px; font-weight:800; font-size:15px;">بيانات العميل</h3>
                <div style="display:grid; gap:10px;">
                    <div>
                        <div style="font-size:12px; color:#6b7280; font-weight:600;">الاسم</div>
                        <div style="font-weight:800; color:#111827;">{{ $order->displayCustomerName() }}</div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#6b7280; font-weight:600;">رقم الهاتف</div>
                        <div style="font-weight:800;">
                            @if ($order->displayPhone() !== '—')
                                <a href="tel:{{ $order->displayPhone() }}" style="color:#1d4ed8; text-decoration:none;">{{ $order->displayPhone() }}</a>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#6b7280; font-weight:600;">العنوان</div>
                        <div style="font-weight:700; color:#374151; line-height:1.6;">{{ $order->displayAddress() }}</div>
                    </div>
                    @if ($order->remote_jid)
                        <div>
                            <div style="font-size:12px; color:#6b7280; font-weight:600;">واتساب</div>
                            <div style="font-family:monospace; font-size:13px; direction:ltr; text-align:right; color:#4b5563;">{{ $order->remote_jid }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- تغيير الحالة --}}
            <div style="border:1px solid #efe3b7; border-radius:12px; padding:14px; background:#fff;">
                <h3 style="margin:0 0 12px; font-weight:800; font-size:15px;">تغيير الحالة</h3>
                <form method="POST" action="{{ route('orders.status', $order) }}">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">الحالة الجديدة</label>
                        <select name="status" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">ملاحظة (اختياري)</label>
                        <input type="text" name="status_note" placeholder="سبب التغيير..."
                               style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                    </div>
                    <label style="display:flex; align-items:center; gap:8px; margin-bottom:12px; cursor:pointer; font-size:13px;">
                        <input type="checkbox" name="notify_customer" value="1" style="width:16px; height:16px; accent-color:#16a34a;">
                        <span>إرسال إشعار واتساب للعميل</span>
                    </label>
                    <button type="submit" style="width:100%; border:none; background:#d4af37; color:#111827; padding:10px; border-radius:8px; font-weight:800; font-family:inherit; cursor:pointer;">
                        تحديث الحالة
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="card" style="margin-bottom:14px;">
        <h3 style="margin:0 0 14px; font-weight:800;">تفاصيل الطلب</h3>

        @if ($order->items->isNotEmpty())
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:640px;">
                    <thead>
                        <tr style="background:#f8f2de;">
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">المنتج</th>
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:center; width:100px;">الكمية</th>
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:center; width:120px;">السعر</th>
                            <th style="padding:10px; border:1px solid #efe3b7; text-align:center; width:130px;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td style="padding:10px; border:1px solid #efe3b7; font-weight:700;">{{ $item->product_title }}</td>
                                <td style="padding:10px; border:1px solid #efe3b7; text-align:center; font-weight:700;">{{ $item->quantity }}</td>
                                <td style="padding:10px; border:1px solid #efe3b7; text-align:center; font-weight:700;">{{ number_format((float) $item->unit_price, 2) }} د.ك</td>
                                <td style="padding:10px; border:1px solid #efe3b7; text-align:center; font-weight:800;">{{ number_format((float) $item->line_total, 2) }} د.ك</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif (!empty($order->items_text))
            <div style="border:1px solid #efe3b7; border-radius:10px; padding:14px; background:#fffcf2; margin-bottom:14px;">
                <div style="font-weight:700; margin-bottom:8px; color:#6b7280; font-size:13px;">المنتجات (نص)</div>
                <div style="white-space:pre-wrap; line-height:1.8; font-weight:600; color:#374151;">{{ $order->items_text }}</div>
            </div>
        @else
            <p style="color:#6b7280; margin:0 0 14px;">لا توجد منتجات مسجلة في هذا الطلب.</p>
        @endif

        <div style="margin-top:16px; max-width:360px; margin-right:auto; margin-left:0;">
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #efe3b7;">
                <span style="color:#6b7280; font-weight:600;">المجموع الفرعي</span>
                <span style="font-weight:800;">{{ number_format($subtotal, 2) }} د.ك</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #efe3b7;">
                <span style="color:#6b7280; font-weight:600;">التوصيل</span>
                <span style="font-weight:800;">{{ number_format($deliveryFee, 2) }} د.ك</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:12px 0 4px;">
                <span style="font-weight:800; font-size:16px;">الإجمالي</span>
                <span style="font-weight:900; font-size:18px; color:#111827;">{{ number_format($grandTotal, 2) }} د.ك</span>
            </div>
        </div>
    </section>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:14px;">
        {{-- سجل الحالات --}}
        <section class="card">
            <h3 style="margin:0 0 14px; font-weight:800;">سجل تاريخ الحالات</h3>
            @if ($order->statusHistories->isEmpty())
                <p style="margin:0; color:#6b7280;">لا يوجد سجل بعد.</p>
            @else
                <div style="display:grid; gap:0;">
                    @foreach ($order->statusHistories as $history)
                        <div style="display:flex; gap:12px; padding:12px 0; border-bottom:1px solid #f3f4f6;">
                            <div style="width:10px; height:10px; border-radius:50%; background:#d4af37; margin-top:6px; flex-shrink:0;"></div>
                            <div style="flex:1;">
                                <div style="font-weight:800; color:#111827; margin-bottom:4px;">{{ $history->status }}</div>
                                <div style="font-size:12px; color:#6b7280;">
                                    {{ $history->created_at?->format('d/m/Y h:i A') }}
                                    @if ($history->changed_by)
                                        · {{ $history->changed_by }}
                                    @endif
                                </div>
                                @if ($history->note)
                                    <div style="font-size:13px; color:#4b5563; margin-top:4px;">{{ $history->note }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ملاحظات داخلية --}}
        <section class="card">
            <h3 style="margin:0 0 14px; font-weight:800;">ملاحظات داخلية للموظف</h3>
            <form method="POST" action="{{ route('orders.notes', $order) }}">
                @csrf
                @method('PUT')
                <div style="margin-bottom:10px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">رسوم التوصيل (د.ك)</label>
                    <input type="number" name="delivery_fee" step="0.01" min="0" value="{{ old('delivery_fee', $order->delivery_fee) }}"
                           style="width:100%; max-width:200px; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">ملاحظات</label>
                    <textarea name="internal_notes" rows="5" placeholder="ملاحظات خاصة بالموظفين فقط..."
                              style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;">{{ old('internal_notes', $order->internal_notes) }}</textarea>
                </div>
                <button type="submit" style="border:none; background:#111827; color:#fff; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                    حفظ الملاحظات
                </button>
            </form>
        </section>
    </div>
@endsection
