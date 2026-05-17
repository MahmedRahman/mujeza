<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Support\ReportPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TopProductsReportsService
{
    /**
     * @return array{
     *     generated_at: Carbon,
     *     period: array{
     *         key: string,
     *         label: string,
     *         from_label: string,
     *         products: array<int, array{rank: int, title: string, quantity: int, orders_count: int}>
     *     }
     * }
     */
    public function topOrderedProducts(?string $periodKey = null, int $limit = 5): array
    {
        $period   = ReportPeriod::resolve($periodKey);
        $products = Product::query()->orderByDesc('id')->get(['id', 'title']);

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
                'from_label' => $period['from_label'],
                'products'   => $this->aggregateTopProducts($orders, $products, $limit),
            ],
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @param  Collection<int, Product>  $products
     * @return array<int, array{rank: int, title: string, quantity: int, orders_count: int}>
     */
    private function aggregateTopProducts(Collection $orders, Collection $products, int $limit): array
    {
        /** @var array<string, array{title: string, quantity: int, orders_count: int}> $totals */
        $totals = [];

        foreach ($orders as $order) {
            if ($order->items->isNotEmpty()) {
                foreach ($order->items as $item) {
                    $key = $item->product_id
                        ? 'id:'.$item->product_id
                        : 'title:'.mb_strtolower(trim((string) $item->product_title));

                    if (! isset($totals[$key])) {
                        $totals[$key] = [
                            'title'         => trim((string) $item->product_title),
                            'quantity'      => 0,
                            'orders_count'  => 0,
                        ];
                    }

                    $totals[$key]['quantity'] += max(1, (int) $item->quantity);
                    $totals[$key]['orders_count']++;
                }

                continue;
            }

            $itemsText = trim((string) ($order->items_text ?? ''));
            if ($itemsText === '') {
                continue;
            }

            $this->matchProductsInItemsText($itemsText, $products, $totals);
        }

        return collect($totals)
            ->sortByDesc('quantity')
            ->take($limit)
            ->values()
            ->map(fn (array $row, int $index) => [
                'rank'         => $index + 1,
                'title'        => $row['title'],
                'quantity'     => (int) $row['quantity'],
                'orders_count' => (int) $row['orders_count'],
            ])
            ->all();
    }

    /**
     * @param  array<string, array{title: string, quantity: int, orders_count: int}>  $totals
     */
    private function matchProductsInItemsText(string $itemsText, Collection $products, array &$totals): void
    {
        $haystack = mb_strtolower($itemsText);

        $sortedProducts = $products
            ->filter(fn (Product $product) => mb_strlen(trim((string) $product->title)) >= 3)
            ->sortByDesc(fn (Product $product) => mb_strlen(trim((string) $product->title)));

        foreach ($sortedProducts as $product) {
            $title = trim((string) $product->title);
            $titleLower = mb_strtolower($title);

            if (! str_contains($haystack, $titleLower)) {
                continue;
            }

            $key = 'id:'.$product->id;
            $qty = $this->guessQuantityFromText($itemsText, $title);

            if (! isset($totals[$key])) {
                $totals[$key] = [
                    'title'        => $title,
                    'quantity'     => 0,
                    'orders_count' => 0,
                ];
            }

            $totals[$key]['quantity'] += $qty;
            $totals[$key]['orders_count']++;
        }
    }

    private function guessQuantityFromText(string $text, string $title): int
    {
        $escaped = preg_quote($title, '/');
        $patterns = [
            '/'.$escaped.'\s*[x×]\s*(\d+)/iu',
            '/'.$escaped.'\s+(\d+)\s*(?:عبوة|علبة|حبة|قطعة|unit|pcs)?/iu',
            '/(\d+)\s+'.$escaped.'/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $qty = (int) ($matches[1] ?? 1);

                return max(1, min($qty, 999));
            }
        }

        return 1;
    }
}
