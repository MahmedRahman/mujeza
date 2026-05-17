<?php

namespace App\Http\Controllers;

use App\Services\ComplaintReportsService;
use App\Services\ConversationReportsService;
use App\Services\OrderReportsService;
use App\Services\TopAskedProductsReportsService;
use App\Services\TopProductsReportsService;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function conversationsCount(Request $request, ConversationReportsService $reports): View
    {
        $period = ReportPeriod::resolve($request->query('period'));

        return view('dashboard.reports.conversations-count', $this->viewData($period, [
            'report' => $reports->conversationCounts($period['key']),
        ]));
    }

    public function ordersSummary(Request $request, OrderReportsService $reports): View
    {
        $period = ReportPeriod::resolve($request->query('period'));

        return view('dashboard.reports.orders-summary', $this->viewData($period, [
            'report' => $reports->ordersSummary($period['key']),
        ]));
    }

    public function ordersByStatus(Request $request, OrderReportsService $reports): View
    {
        $period = ReportPeriod::resolve($request->query('period'));

        return view('dashboard.reports.orders-by-status', $this->viewData($period, [
            'report' => $reports->ordersByStatus($period['key']),
        ]));
    }

    public function complaintsSummary(Request $request, ComplaintReportsService $reports): View
    {
        $period = ReportPeriod::resolve($request->query('period'));

        return view('dashboard.reports.complaints-summary', $this->viewData($period, [
            'report' => $reports->complaintsSummary($period['key']),
        ]));
    }

    public function topProducts(Request $request, TopProductsReportsService $reports): View
    {
        $period = ReportPeriod::resolve($request->query('period'));

        return view('dashboard.reports.top-products', $this->viewData($period, [
            'report' => $reports->topOrderedProducts($period['key']),
        ]));
    }

    public function topAskedProducts(Request $request, TopAskedProductsReportsService $reports): View
    {
        $period = ReportPeriod::resolve($request->query('period'));

        return view('dashboard.reports.top-asked-products', $this->viewData($period, [
            'report' => $reports->topAskedProducts($period['key']),
        ]));
    }

    /**
     * @param  array{key: string, label: string, from: \Illuminate\Support\Carbon, from_label: string}  $period
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function viewData(array $period, array $data): array
    {
        return array_merge($data, [
            'period'        => $period['key'],
            'periodOptions' => ReportPeriod::options(),
        ]);
    }
}
