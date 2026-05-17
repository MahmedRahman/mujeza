@php
    $currentStep = $order->trackingStepIndex();
    $isCancelled = $order->status === 'ملغي';
    $statusHistories = $order->relationLoaded('statusHistories')
        ? $order->statusHistories->sortBy('created_at')->values()
        : collect();
@endphp

<div style="border:1px solid #efe3b7; border-radius:14px; padding:16px; background:linear-gradient(180deg, #fffcf2 0%, #fff 100%); margin-bottom:14px;">
    <h3 style="margin:0 0 14px; font-weight:800; font-size:15px;">تتبع حالة الطلب</h3>

    @if ($isCancelled)
        <div style="text-align:center; padding:10px; border-radius:10px; background:#fff1f2; border:1px solid #fecaca; color:#b91c1c; font-weight:800; margin-bottom:12px;">
            تم إلغاء هذا الطلب
        </div>
    @endif

    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:4px; overflow-x:auto; padding-bottom:6px; margin-bottom:14px;">
        @foreach (\App\Models\Order::TRACKING_STATUSES as $index => $stepStatus)
            @php
                $isDone = ! $isCancelled && $currentStep >= $index;
                $isCurrent = ! $isCancelled && $currentStep === $index;
                $dotBg = $isCurrent ? '#d4af37' : ($isDone ? '#15803d' : '#e5e7eb');
                $dotColor = $isCurrent || $isDone ? '#fff' : '#9ca3af';
                $labelColor = $isCurrent ? '#92400e' : ($isDone ? '#15803d' : '#9ca3af');
                $lineColor = $isDone && $index < count(\App\Models\Order::TRACKING_STATUSES) - 1 ? '#15803d' : '#e5e7eb';
            @endphp
            <div style="flex:1; min-width:72px; text-align:center; position:relative;">
                @if ($index < count(\App\Models\Order::TRACKING_STATUSES) - 1)
                    <div style="position:absolute; top:16px; right:calc(50% + 16px); left:calc(-50% + 16px); height:3px; background:{{ $lineColor }}; z-index:0;"></div>
                @endif
                <div style="width:32px; height:32px; border-radius:50%; margin:0 auto 6px; background:{{ $dotBg }}; color:{{ $dotColor }}; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; position:relative; z-index:1; box-shadow:{{ $isCurrent ? '0 0 0 3px rgba(212,175,55,.25)' : 'none' }};">
                    @if ($isDone && ! $isCurrent)
                        ✓
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
                <div style="font-size:10px; font-weight:{{ $isCurrent ? '800' : '700' }}; color:{{ $labelColor }}; line-height:1.3;">
                    {{ $stepStatus }}
                </div>
            </div>
        @endforeach
    </div>

    <h4 style="margin:0 0 10px; font-weight:800; font-size:14px;">سجل تاريخ الحالات</h4>
    @if ($statusHistories->isEmpty())
        <p style="margin:0; color:#6b7280; font-size:13px;">لا يوجد سجل بعد.</p>
    @else
        <div style="display:grid; gap:0; max-height:220px; overflow-y:auto;">
            @foreach ($statusHistories as $history)
                <div style="display:flex; gap:10px; padding:10px 0; border-bottom:1px solid #f3f4f6;">
                    <div style="width:8px; height:8px; border-radius:50%; background:#d4af37; margin-top:6px; flex-shrink:0;"></div>
                    <div style="flex:1;">
                        <div style="font-weight:800; color:#111827; font-size:13px; margin-bottom:3px;">{{ $history->status }}</div>
                        <div style="font-size:12px; color:#6b7280;">
                            {{ $history->created_at?->format('d/m/Y h:i A') }}
                            @if ($history->changed_by)
                                · {{ $history->changed_by }}
                            @endif
                        </div>
                        @if ($history->note)
                            <div style="font-size:12px; color:#4b5563; margin-top:3px;">{{ $history->note }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
