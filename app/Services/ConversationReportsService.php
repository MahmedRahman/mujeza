<?php

namespace App\Services;

use App\Http\Controllers\WhatsAppController;
use App\Support\ReportPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ConversationReportsService
{
    public function __construct(
        private readonly WhatsAppController $whatsAppController,
    ) {}

    /**
     * @return array{
     *     error: string|null,
     *     generated_at: Carbon|null,
     *     total_chats: int,
     *     period: array{key: string, label: string, count: int, from_label: string}
     * }
     */
    public function conversationCounts(?string $periodKey = null): array
    {
        $period = ReportPeriod::resolve($periodKey);
        $fetch  = $this->whatsAppController->getDirectChatsForReporting();

        if ($fetch['error'] !== null) {
            return [
                'error'        => $fetch['error'],
                'generated_at' => null,
                'total_chats'  => 0,
                'period'       => [
                    'key'        => $period['key'],
                    'label'      => $period['label'],
                    'count'      => 0,
                    'from_label' => $period['from_label'],
                ],
            ];
        }

        /** @var Collection<int, array<string, mixed>> $chats */
        $chats  = $fetch['chats'];
        $fromTs = $period['from']->timestamp;

        return [
            'error'        => null,
            'generated_at' => $fetch['generated_at'],
            'total_chats'  => $chats->count(),
            'period'       => [
                'key'        => $period['key'],
                'label'      => $period['label'],
                'count'      => $chats->filter(fn (array $chat) => $this->chatActivityTimestamp($chat) >= $fromTs)->count(),
                'from_label' => $period['from_label'],
            ],
        ];
    }

    private function chatActivityTimestamp(array $chat): int
    {
        $ts = (int) ($chat['timestamp'] ?? 0);

        if ($ts > 9999999999) {
            return (int) floor($ts / 1000);
        }

        return $ts;
    }
}
