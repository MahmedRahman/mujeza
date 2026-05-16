<?php

namespace App\Services;

use App\Http\Controllers\WhatsAppController;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;

class DashboardAlertsService
{
    public function __construct(
        private readonly WhatsAppController $whatsAppController,
    ) {}

    /**
     * @return array{
     *     unconfirmed_orders: Collection<int, Order>,
     *     new_complaints: Collection<int, Complaint>,
     *     human_chats: array{total: int, items: array<int, array<string, mixed>>, error: string|null},
     *     frequent_products: Collection<int, array{product: Product, mentions: int, needs_ai_update: bool}>
     * }
     */
    public function build(): array
    {
        return [
            'unconfirmed_orders' => $this->unconfirmedOrders(),
            'new_complaints' => $this->newComplaints(),
            'human_chats' => $this->whatsAppController->humanInterventionChats(8),
            'frequent_products' => $this->frequentlyAskedProducts(8),
        ];
    }

    public function hasAlerts(array $alerts): bool
    {
        return $alerts['unconfirmed_orders']->isNotEmpty()
            || $alerts['new_complaints']->isNotEmpty()
            || ($alerts['human_chats']['total'] ?? 0) > 0
            || $alerts['frequent_products']->isNotEmpty();
    }

    private function unconfirmedOrders(): Collection
    {
        return Order::query()
            ->where('status', Order::DEFAULT_STATUS)
            ->latest()
            ->limit(10)
            ->get(['id', 'order_number', 'customer_name', 'phone', 'status', 'created_at', 'remote_jid']);
    }

    private function newComplaints(): Collection
    {
        return Complaint::query()
            ->where('status', 'جديدة')
            ->latest()
            ->limit(10)
            ->get(['id', 'title', 'status', 'remote_jid', 'created_at']);
    }

    private function frequentlyAskedProducts(int $limit): Collection
    {
        $since = now()->subDays(30);

        $textBlobs = collect()
            ->merge(Order::query()->where('created_at', '>=', $since)->pluck('items_text'))
            ->merge(Complaint::query()->where('created_at', '>=', $since)->pluck('title'))
            ->merge(Complaint::query()->where('created_at', '>=', $since)->pluck('description'))
            ->filter(fn ($text) => is_string($text) && trim($text) !== '')
            ->implode("\n");

        if ($textBlobs === '') {
            return collect();
        }

        $haystack = mb_strtolower($textBlobs);

        return Product::query()
            ->orderBy('title')
            ->get()
            ->map(function (Product $product) use ($haystack) {
                $title = trim($product->title);
                if ($title === '' || mb_strlen($title) < 4) {
                    return null;
                }

                $mentions = substr_count($haystack, mb_strtolower($title));
                if ($mentions < 2) {
                    return null;
                }

                return [
                    'product' => $product,
                    'mentions' => $mentions,
                    'needs_ai_update' => $this->productNeedsAiUpdate($product),
                ];
            })
            ->filter()
            ->sortByDesc('mentions')
            ->take($limit)
            ->values();
    }

    private function productNeedsAiUpdate(Product $product): bool
    {
        $benefits = collect($product->benefits ?? [])->filter(fn ($v) => trim((string) $v) !== '');
        $diseases = collect($product->diseases ?? [])->filter(fn ($v) => trim((string) $v) !== '');
        $usage = collect($product->usage_methods ?? [])->filter(fn ($v) => trim((string) $v) !== '');
        $description = trim((string) $product->description);

        return $benefits->isEmpty()
            || $diseases->isEmpty()
            || $usage->isEmpty()
            || mb_strlen($description) < 40;
    }
}
