<?php

namespace App\Services;

use App\Models\Complaint;
use App\Support\ReportPeriod;
use Illuminate\Support\Carbon;

class ComplaintReportsService
{
    public const RESOLVED_STATUSES = ['تم الحل', 'مغلقة'];

    /**
     * @return array{
     *     generated_at: Carbon,
     *     period: array{
     *         key: string,
     *         label: string,
     *         count: int,
     *         resolved: int,
     *         resolution_rate: float,
     *         from_label: string
     *     }
     * }
     */
    public function complaintsSummary(?string $periodKey = null): array
    {
        $period = ReportPeriod::resolve($periodKey);

        $complaints = Complaint::query()
            ->where('created_at', '>=', $period['from'])
            ->get();

        $count    = $complaints->count();
        $resolved = $complaints->whereIn('status', self::RESOLVED_STATUSES)->count();

        return [
            'generated_at' => now(),
            'period'         => [
                'key'             => $period['key'],
                'label'           => $period['label'],
                'count'           => $count,
                'resolved'        => $resolved,
                'resolution_rate' => $count > 0 ? round(($resolved / $count) * 100, 1) : 0.0,
                'from_label'      => $period['from_label'],
            ],
        ];
    }
}
