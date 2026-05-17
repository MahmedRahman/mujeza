@php
    $notes = $order->parsedItemsNotes();
@endphp

@if ($notes)
    <div style="border:1px solid #e5e7eb; border-radius:12px; padding:14px; background:#f9fafb; margin-bottom:16px;">
        <div style="font-weight:800; margin-bottom:12px; color:#374151; font-size:14px;">ملاحظات</div>

        @if ($notes['format'] === 'json')
            @if (! empty($notes['meta']))
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:10px; margin-bottom:14px;">
                    @foreach ($notes['meta'] as $label => $value)
                        <div style="background:#fff; border:1px solid #efe3b7; border-radius:8px; padding:10px 12px;">
                            <div style="font-size:11px; color:#6b7280; font-weight:700; margin-bottom:4px;">{{ $label }}</div>
                            <div style="font-weight:800; color:#111827; font-size:14px; line-height:1.4;">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (! empty($notes['items']))
                <div style="font-size:12px; color:#6b7280; font-weight:700; margin-bottom:8px;">المنتجات المطلوبة (من المحادثة)</div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width:400px;">
                        <thead>
                            <tr style="background:#f3f4f6;">
                                <th style="padding:8px 10px; border:1px solid #e5e7eb; text-align:right;">المنتج</th>
                                <th style="padding:8px 10px; border:1px solid #e5e7eb; text-align:center; width:80px;">الكمية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notes['items'] as $row)
                                <tr>
                                    <td style="padding:8px 10px; border:1px solid #e5e7eb; font-weight:600; color:#374151;">{{ $row['product'] }}</td>
                                    <td style="padding:8px 10px; border:1px solid #e5e7eb; text-align:center; font-weight:800;">{{ $row['quantity'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif (empty($notes['meta']))
                <p style="margin:0; color:#6b7280; font-weight:600; font-size:13px;">لا توجد تفاصيل قابلة للعرض في الملاحظات.</p>
            @endif
        @else
            <div style="background:#fff; border:1px solid #efe3b7; border-radius:8px; padding:12px 14px; color:#374151; font-weight:600; line-height:1.8; white-space:pre-wrap; word-break:break-word;">
                {{ $notes['text'] }}
            </div>
        @endif
    </div>
@endif
