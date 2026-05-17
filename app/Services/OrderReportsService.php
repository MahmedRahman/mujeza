<?php

namespace App\Services;

use App\Models\Order;
use App\Support\ReportPeriod;
use Illuminate\Support\Carbon;

class OrderReportsService
{
    /**
     * @return array{
     *     generated_at: Carbon,
     *     period: array{key: string, label: string, count: int, total: float, from_label: string}
     * }
     */
    public function ordersSummary(?string $periodKey = null): array
    {
        $period = ReportPeriod::resolve($periodKey);

        $orders = Order::query()
            ->with('items')
            ->where('created_at', '>=', $period['from'])
            ->where('status', '!=', 'ملغي')
            ->get();

        return [
            'generated_at' => now(),
            'period'         => [
                'key'        => $period['key'],
                'label'      => $period['label'],
                'count'      => $orders->count(),
                'total'      => round($orders->sum(fn (Order $order) => $order->grandTotal()), 3),
                'from_label' => $period['from_label'],
            ],
        ];
    }

    /**
     * @return array{
     *     generated_at: Carbon,
     *     period: array{
     *         key: string,
     *         label: string,
     *         from_label: string,
     *         total: int,
     *         statuses: array<int, array{status: string, count: int, percent: float}>
     *     }
     * }
     */
    public function ordersByStatus(?string $periodKey = null): array
    {
        $period = ReportPeriod::resolve($periodKey);

        $orders  = Order::query()->where('created_at', '>=', $period['from'])->get(['status']);
        $total   = $orders->count();
        $grouped = $orders->countBy('status');

        $knownStatuses = Order::STATUSES;
        $statusRows    = [];

        foreach ($knownStatuses as $status) {
            $count = (int) ($grouped[$status] ?? 0);
            $statusRows[] = [
                'status'  => $status,
                'count'   => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        foreach ($grouped as $status => $count) {
            if (in_array($status, $knownStatuses, true)) {
                continue;
            }

            $statusRows[] = [
                'status'  => (string) $status,
                'count'   => (int) $count,
                'percent' => $total > 0 ? round(((int) $count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'generated_at' => now(),
            'period'         => [
                'key'        => $period['key'],
                'label'      => $period['label'],
                'from_label' => $period['from_label'],
                'total'      => $total,
                'statuses'   => $statusRows,
            ],
        ];
    }
}
