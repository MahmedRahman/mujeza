<?php

namespace App\Services;

use App\Http\Controllers\WhatsAppController;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Support\ReportPeriod;
use Illuminate\Support\Carbon;

class TopAskedProductsReportsService
{
    public function __construct(
        private readonly WhatsAppController $whatsAppController,
    ) {}

    /**
     * @return array{
     *     generated_at: Carbon,
     *     period: array{
     *         key: string,
     *         label: string,
     *         from_label: string,
     *         products: array<int, array{rank: int, title: string, mentions: int}>
     *     }
     * }
     */
    public function topAskedProducts(?string $periodKey = null, int $limit = 5): array
    {
        $period   = ReportPeriod::resolve($periodKey);
        $products = Product::query()->orderBy('title')->get(['id', 'title']);
        $haystack = mb_strtolower($this->buildTextCorpus($period['from']));

        $ranked = $products
            ->map(function (Product $product) use ($haystack) {
                $title = trim((string) $product->title);
                if ($title === '' || mb_strlen($title) < 3) {
                    return null;
                }

                $mentions = $haystack === ''
                    ? 0
                    : substr_count($haystack, mb_strtolower($title));

                if ($mentions < 1) {
                    return null;
                }

                return [
                    'title'    => $title,
                    'mentions' => $mentions,
                ];
            })
            ->filter()
            ->sortByDesc('mentions')
            ->take($limit)
            ->values()
            ->map(fn (array $row, int $index) => [
                'rank'     => $index + 1,
                'title'    => $row['title'],
                'mentions' => (int) $row['mentions'],
            ])
            ->all();

        return [
            'generated_at' => now(),
            'period'         => [
                'key'        => $period['key'],
                'label'      => $period['label'],
                'from_label' => $period['from_label'],
                'products'   => $ranked,
            ],
        ];
    }

    private function buildTextCorpus(Carbon $from): string
    {
        $texts = [];

        Order::query()
            ->where('created_at', '>=', $from)
            ->pluck('items_text')
            ->each(function ($text) use (&$texts) {
                if (is_string($text) && trim($text) !== '') {
                    $texts[] = trim($text);
                }
            });

        Complaint::query()
            ->where('created_at', '>=', $from)
            ->get(['title', 'description'])
            ->each(function (Complaint $complaint) use (&$texts) {
                $title = trim((string) $complaint->title);
                $description = trim((string) $complaint->description);

                if ($title !== '') {
                    $texts[] = $title;
                }
                if ($description !== '') {
                    $texts[] = $description;
                }
            });

        $chatsFetch = $this->whatsAppController->getDirectChatsForReporting();
        if ($chatsFetch['error'] === null) {
            $fromTs = $from->timestamp;

            foreach ($chatsFetch['chats'] as $chat) {
                $timestamp = (int) ($chat['timestamp'] ?? 0);
                if ($timestamp > 9999999999) {
                    $timestamp = (int) floor($timestamp / 1000);
                }

                if ($timestamp < $fromTs) {
                    continue;
                }

                $message = trim((string) ($chat['last_message'] ?? ''));
                if ($message !== '' && $message !== '...') {
                    $texts[] = $message;
                }
            }
        }

        return implode("\n", $texts);
    }
}
