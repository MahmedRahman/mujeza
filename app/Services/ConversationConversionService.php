<?php

namespace App\Services;

use App\Support\ReportPeriod;
use Illuminate\Support\Carbon;

class ConversationConversionService
{
    public function __construct(
        private readonly ConversationReportsService $conversationReports,
        private readonly OrderReportsService $orderReports,
    ) {}

    /**
     * نسبة تحويل المحادثة إلى طلب = عدد الطلبات ÷ عدد المحادثات الفريدة × 100
     *
     * @return array{
     *     error: string|null,
     *     generated_at: Carbon|null,
     *     period: array{key: string, label: string, from_label: string},
     *     orders_count: int,
     *     conversations_count: int,
     *     conversion_rate: float|null
     * }
     */
    public function summary(?string $periodKey = null): array
    {
        $period = ReportPeriod::resolve($periodKey);

        $conversations = $this->conversationReports->conversationCounts($period['key']);
        $orders        = $this->orderReports->ordersSummary($period['key']);

        $conversationsCount = (int) $conversations['period']['count'];
        $ordersCount        = (int) $orders['period']['count'];

        $rate = $conversationsCount > 0
            ? round(($ordersCount / $conversationsCount) * 100, 1)
            : null;

        return [
            'error'                => $conversations['error'],
            'generated_at'         => $conversations['generated_at'] ?? $orders['generated_at'],
            'period'               => [
                'key'        => $period['key'],
                'label'      => $period['label'],
                'from_label' => $period['from_label'],
            ],
            'orders_count'         => $ordersCount,
            'conversations_count'  => $conversationsCount,
            'conversion_rate'      => $rate,
        ];
    }
}
