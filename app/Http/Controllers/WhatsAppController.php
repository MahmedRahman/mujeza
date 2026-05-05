<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class WhatsAppController extends Controller
{
    private const AUTO_REPLY_GLOBAL_KEY = 'whatsapp_auto_reply_global_enabled';
    private const AUTO_REPLY_CHAT_OVERRIDES_KEY = 'whatsapp_auto_reply_chat_overrides';

    private function evoSettings(): array
    {
        $stored = Setting::query()
            ->whereIn('key', ['evo_url', 'evo_api_key', 'evo_instance'])
            ->pluck('value', 'key')
            ->toArray();

        return [
            'url'      => rtrim($stored['evo_url'] ?? '', '/'),
            'key'      => $stored['evo_api_key'] ?? '',
            'instance' => $stored['evo_instance'] ?? '',
        ];
    }

    private function evoHttp(array $cfg): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'apikey'       => $cfg['key'],
            'Content-Type' => 'application/json',
        ])->timeout(15);
    }

    /** Strip @s.whatsapp.net / @g.us to get plain number/group-id */
    private function jidToNumber(string $jid): string
    {
        return preg_replace('/@[^@]+$/', '', $jid);
    }

    private function normalizeChatName(?string $name, string $chatId): string
    {
        $clean = trim((string) $name);
        if ($clean === '') {
            return '';
        }

        $lower = mb_strtolower($clean, 'UTF-8');
        $invalidNames = [
            'você',
            'voce',
            'you',
            'me',
            'أنا',
            'انا',
            'unknown',
            'null',
        ];

        if (in_array($lower, $invalidNames, true)) {
            return '';
        }

        // Reject numeric-only / identifier-like names (e.g. "13135550202").
        $nameDigits = preg_replace('/\D+/', '', $clean) ?? '';
        if ($nameDigits !== '' && strlen($nameDigits) >= 8 && $nameDigits === $clean) {
            return '';
        }

        return $clean;
    }

    private function normalizeChatPhone(string $chatId): ?string
    {
        $chatId = trim($chatId);
        $domain = $this->chatDomain($chatId);

        // Only direct WhatsApp user chats should be treated as phone numbers.
        if (! in_array($domain, ['s.whatsapp.net', 'c.us'], true)) {
            return null;
        }

        $raw = trim($this->jidToNumber($chatId));
        if ($raw === '') {
            return null;
        }

        // Some providers append device info (e.g. 2010...:12), keep only main part.
        $raw = preg_split('/[:;_]/', $raw)[0] ?? $raw;
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        // Basic sanity check for phone length; otherwise it's likely a non-phone identifier.
        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        // Exclude obvious placeholder/test numbers (e.g. NANP 555 exchange).
        if (preg_match('/^1\d{3}555\d{4}$/', $digits) === 1) {
            return null;
        }

        return $hasPlus ? '+'.$digits : $digits;
    }

    private function chatDomain(string $chatId): string
    {
        return strtolower((string) preg_replace('/^.*@/', '', trim($chatId)));
    }

    private function resolveChatName(array $chat, string $chatId, ?string $phone): string
    {
        $candidates = [
            data_get($chat, 'contactName'),
            data_get($chat, 'name'),
            data_get($chat, 'pushName'),
            data_get($chat, 'lastMessage.pushName'),
            data_get($chat, 'lastMessage.message.contactMessage.displayName'),
            data_get($chat, 'lastMessage.message.extendedTextMessage.contextInfo.quotedMessage.contactMessage.displayName'),
        ];

        foreach ($candidates as $candidate) {
            $name = $this->normalizeChatName(is_string($candidate) ? $candidate : null, $chatId);
            if ($name === '') {
                continue;
            }

            if ($phone !== null) {
                $nameDigits = preg_replace('/\D+/', '', $name) ?? '';
                if ($nameDigits !== '' && $nameDigits === $phone) {
                    continue;
                }
            }

            return $name;
        }

        $domain = strtolower((string) preg_replace('/^.*@/', '', $chatId));
        if ($domain === 'g.us') {
            return 'مجموعة واتساب';
        }
        if ($domain === 'status') {
            return 'حالات واتساب';
        }

        return 'عميل واتساب';
    }

    private function autoReplyGlobalEnabled(): bool
    {
        $raw = (string) (Setting::query()->where('key', self::AUTO_REPLY_GLOBAL_KEY)->value('value') ?? '0');

        return in_array(strtolower($raw), ['1', 'true', 'on', 'yes'], true);
    }

    private function autoReplyChatOverrides(): array
    {
        $raw = Setting::query()->where('key', self::AUTO_REPLY_CHAT_OVERRIDES_KEY)->value('value');
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->mapWithKeys(fn ($value, $chatId) => [(string) $chatId => (bool) $value])
            ->all();
    }

    private function normalizeInputPhone(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        return $hasPlus ? '+'.$digits : $digits;
    }

    private function mapChatForUi(array $c, bool $globalAutoReplyEnabled, array $chatOverrides): array
    {
        $chatId = (string) $c['remoteJid'];
        $hasOverride = array_key_exists($chatId, $chatOverrides);
        $phone = $this->normalizeChatPhone($chatId);
        $name = $this->resolveChatName($c, $chatId, $phone);
        $timestamp = (int) data_get($c, 'lastMessage.messageTimestamp', 0);
        $lastMessage = data_get($c, 'lastMessage.message.conversation')
            ?? data_get($c, 'lastMessage.message.extendedTextMessage.text')
            ?? '...';

        return [
            'id' => $chatId,
            'name' => $name,
            'phone' => $phone,
            'last_message' => $lastMessage,
            'timestamp' => $timestamp,
            'unread' => $c['unreadCount'] ?? 0,
            'auto_reply_enabled' => $hasOverride ? (bool) $chatOverrides[$chatId] : $globalAutoReplyEnabled,
            'auto_reply_overridden' => $hasOverride,
            '_domain' => $this->chatDomain($chatId),
        ];
    }

    private function buildChatsCollection(array $rawChats, bool $globalAutoReplyEnabled, array $chatOverrides)
    {
        return collect($rawChats)
            ->filter(fn ($c) => isset($c['remoteJid']))
            ->map(fn ($c) => $this->mapChatForUi((array) $c, $globalAutoReplyEnabled, $chatOverrides))
            // Keep only direct person-to-person chats with actual message history.
            ->filter(fn ($chat) => in_array($chat['_domain'], ['s.whatsapp.net', 'c.us'], true))
            ->filter(fn ($chat) => (int) ($chat['timestamp'] ?? 0) > 0)
            ->filter(fn ($chat) => trim((string) ($chat['last_message'] ?? '')) !== '')
            ->sortByDesc('timestamp')
            // Deduplicate by phone when available; keep most recent chat only.
            ->unique(fn ($chat) => (string) ($chat['phone'] ?? $chat['id']))
            ->map(function ($chat) {
                unset($chat['_domain']);

                return $chat;
            });
    }

    private function writeSetting(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    private function normalizeMessageTimestamp(mixed $value): int
    {
        $ts = (int) $value;
        if ($ts <= 0) {
            return 0;
        }

        // Some providers send milliseconds, normalize to seconds.
        if ($ts > 9999999999) {
            $ts = (int) floor($ts / 1000);
        }

        return $ts;
    }

    private function normalizeBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
    }

    private function unwrapMessagePayload(array $payload): array
    {
        // Evolution/WhatsApp can nest the actual message inside wrappers.
        $wrapperPaths = [
            'ephemeralMessage.message',
            'viewOnceMessage.message',
            'viewOnceMessageV2.message',
            'viewOnceMessageV2Extension.message',
            'documentWithCaptionMessage.message',
            'editedMessage.message',
            'protocolMessage.editedMessage',
        ];

        $current = $payload;
        $safety = 0;
        while ($safety < 6) {
            $safety++;
            $next = null;
            foreach ($wrapperPaths as $path) {
                $candidate = data_get($current, $path);
                if (is_array($candidate) && $candidate !== []) {
                    $next = $candidate;
                    break;
                }
            }

            if (! is_array($next) || $next === []) {
                break;
            }

            $current = $next;
        }

        return $current;
    }

    private function extractMessageText(array $record): string
    {
        $payload = $this->unwrapMessagePayload((array) data_get($record, 'message', []));

        $text = trim((string) (
            data_get($payload, 'conversation')
            ?? data_get($payload, 'extendedTextMessage.text')
            ?? data_get($payload, 'imageMessage.caption')
            ?? data_get($payload, 'videoMessage.caption')
            ?? data_get($payload, 'documentMessage.caption')
            ?? data_get($payload, 'buttonsResponseMessage.selectedDisplayText')
            ?? data_get($payload, 'listResponseMessage.title')
            ?? data_get($payload, 'listResponseMessage.description')
            ?? data_get($payload, 'templateButtonReplyMessage.selectedDisplayText')
            ?? data_get($payload, 'reactionMessage.text')
            ?? ''
        ));

        if ($text !== '') {
            return $text;
        }

        if (data_get($payload, 'imageMessage') !== null) {
            return '[صورة]';
        }
        if (data_get($payload, 'videoMessage') !== null) {
            return '[فيديو]';
        }
        if (data_get($payload, 'audioMessage') !== null) {
            return '[رسالة صوتية]';
        }
        if (data_get($payload, 'stickerMessage') !== null) {
            return '[ملصق]';
        }
        if (data_get($payload, 'documentMessage') !== null) {
            $fileName = trim((string) data_get($payload, 'documentMessage.fileName', ''));
            return $fileName !== '' ? "[ملف: {$fileName}]" : '[ملف]';
        }
        if (data_get($payload, 'locationMessage') !== null || data_get($payload, 'liveLocationMessage') !== null) {
            return '[موقع]';
        }
        if (data_get($payload, 'contactMessage') !== null) {
            $name = trim((string) data_get($payload, 'contactMessage.displayName', ''));
            return $name !== '' ? "[جهة اتصال: {$name}]" : '[جهة اتصال]';
        }
        if (data_get($payload, 'protocolMessage') !== null) {
            return '[رسالة نظام]';
        }

        return '[رسالة غير مدعومة]';
    }

    public function status(): JsonResponse
    {
        $cfg = $this->evoSettings();

        if (! $cfg['url'] || ! $cfg['instance']) {
            return response()->json(['connected' => false, 'reason' => 'not_configured']);
        }

        $response = $this->evoHttp($cfg)
            ->get("{$cfg['url']}/instance/connectionState/{$cfg['instance']}");

        $body  = $response->json();
        $state = data_get($body, 'instance.state')
            ?? data_get($body, 'state')
            ?? 'unknown';

        return response()->json([
            'connected' => $state === 'open',
            'state'     => $state,
        ]);
    }

    public function chats(): JsonResponse
    {
        $cfg = $this->evoSettings();
        $globalAutoReplyEnabled = $this->autoReplyGlobalEnabled();
        $chatOverrides = $this->autoReplyChatOverrides();

        if (! $cfg['url'] || ! $cfg['instance']) {
            return response()->json(['error' => 'Evolution API not configured.'], 422);
        }

        $response = $this->evoHttp($cfg)
            ->post("{$cfg['url']}/chat/findChats/{$cfg['instance']}", []);

        if (! $response->successful()) {
            Log::error('EVO findChats failed', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json(['error' => 'Failed to fetch chats.', 'details' => $response->body()], $response->status());
        }

        $raw = $response->json();

        // Handle both array and {chats: [...]} shapes
        if (isset($raw['chats'])) {
            $raw = $raw['chats'];
        }

        $chats = $this->buildChatsCollection((array) $raw, $globalAutoReplyEnabled, $chatOverrides)
            ->values();

        return response()->json($chats);
    }

    #[OA\Get(
        path: '/whatsapp/auto-reply/numbers',
        operationId: 'whatsappAutoReplyNumbers',
        tags: ['WhatsApp'],
        summary: 'Get chats numbers with auto-reply state',
        description: 'Returns chats list (number/name) and whether auto-reply is allowed per chat, plus global toggle state.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Auto-reply numbers list fetched successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Evolution API is not configured'
            ),
        ]
    )]
    public function autoReplyNumbers(): JsonResponse
    {
        $cfg = $this->evoSettings();
        $globalAutoReplyEnabled = $this->autoReplyGlobalEnabled();
        $chatOverrides = $this->autoReplyChatOverrides();

        if (! $cfg['url'] || ! $cfg['instance']) {
            return response()->json(['error' => 'Evolution API not configured.'], 422);
        }

        $response = $this->evoHttp($cfg)
            ->post("{$cfg['url']}/chat/findChats/{$cfg['instance']}", []);

        if (! $response->successful()) {
            Log::error('EVO autoReplyNumbers findChats failed', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json(['error' => 'Failed to fetch chats.', 'details' => $response->body()], $response->status());
        }

        $raw = $response->json();
        if (isset($raw['chats'])) {
            $raw = $raw['chats'];
        }

        $numbers = $this->buildChatsCollection((array) $raw, $globalAutoReplyEnabled, $chatOverrides)
            ->map(fn ($chat) => [
                'chat_id' => $chat['id'],
                'name' => $chat['name'],
                'phone' => $chat['phone'],
                'auto_reply_allowed' => (bool) $chat['auto_reply_enabled'],
                'auto_reply_overridden' => (bool) $chat['auto_reply_overridden'],
                'last_message' => $chat['last_message'],
                'last_message_timestamp' => (int) $chat['timestamp'],
            ])
            ->values();

        return response()->json([
            'global_enabled' => $globalAutoReplyEnabled,
            'total' => $numbers->count(),
            'numbers' => $numbers,
        ]);
    }

    #[OA\Get(
        path: '/whatsapp/auto-reply/number-status',
        operationId: 'whatsappAutoReplyNumberStatus',
        tags: ['WhatsApp'],
        summary: 'Get auto-reply status for single chat/phone',
        description: 'Checks whether auto-reply is allowed for a specific chat_id or phone, considering global + override values.',
        parameters: [
            new OA\Parameter(
                name: 'chat_id',
                description: 'WhatsApp chat JID (optional if phone is provided)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: '201011223344@s.whatsapp.net')
            ),
            new OA\Parameter(
                name: 'phone',
                description: 'Phone number in local or international format (optional if chat_id is provided)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: '201011223344')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Auto-reply status evaluated successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function autoReplyNumberStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => ['nullable', 'string', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:50', 'required_without:chat_id'],
        ]);

        $global = $this->autoReplyGlobalEnabled();
        $overrides = $this->autoReplyChatOverrides();

        $chatId = isset($validated['chat_id']) ? trim((string) $validated['chat_id']) : null;
        $phone = $this->normalizeInputPhone($validated['phone'] ?? null);
        $matchedChatId = null;
        $overrideEnabled = null;

        if ($chatId !== null && $chatId !== '') {
            if (array_key_exists($chatId, $overrides)) {
                $matchedChatId = $chatId;
                $overrideEnabled = (bool) $overrides[$chatId];
            }
        } elseif ($phone !== null) {
            foreach ($overrides as $overrideChatId => $enabled) {
                $overridePhone = $this->normalizeChatPhone((string) $overrideChatId);
                if ($overridePhone !== null && $overridePhone === $phone) {
                    $matchedChatId = (string) $overrideChatId;
                    $overrideEnabled = (bool) $enabled;
                    break;
                }
            }
        }

        $allowed = $overrideEnabled ?? $global;

        return response()->json([
            'global_enabled' => $global,
            'chat_id' => $chatId,
            'phone' => $phone,
            'override_found' => $overrideEnabled !== null,
            'override_chat_id' => $matchedChatId,
            'override_enabled' => $overrideEnabled,
            'auto_reply_allowed' => (bool) $allowed,
        ]);
    }

    #[OA\Get(
        path: '/whatsapp/auto-reply/settings',
        operationId: 'whatsappAutoReplySettings',
        tags: ['WhatsApp'],
        summary: 'Get global auto-reply setting',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Global auto-reply setting fetched successfully'
            ),
        ]
    )]
    public function autoReplySettings(): JsonResponse
    {
        return response()->json([
            'global_enabled' => $this->autoReplyGlobalEnabled(),
        ]);
    }

    #[OA\Post(
        path: '/whatsapp/auto-reply/global',
        operationId: 'whatsappUpdateGlobalAutoReply',
        tags: ['WhatsApp'],
        summary: 'Enable or disable global auto-reply',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['enabled'],
                properties: [
                    new OA\Property(property: 'enabled', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Global auto-reply updated successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function updateGlobalAutoReply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = (bool) $validated['enabled'];
        $this->writeSetting(self::AUTO_REPLY_GLOBAL_KEY, $enabled ? '1' : '0');

        return response()->json([
            'ok' => true,
            'global_enabled' => $enabled,
        ]);
    }

    #[OA\Post(
        path: '/whatsapp/auto-reply/chat',
        operationId: 'whatsappUpdateChatAutoReply',
        tags: ['WhatsApp'],
        summary: 'Enable or disable auto-reply for specific chat',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['chat_id', 'enabled'],
                properties: [
                    new OA\Property(property: 'chat_id', type: 'string', example: '201011223344@s.whatsapp.net'),
                    new OA\Property(property: 'enabled', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Per-chat auto-reply override updated successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function updateChatAutoReply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => ['required', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
        ]);

        $chatId = trim((string) $validated['chat_id']);
        $enabled = (bool) $validated['enabled'];
        $overrides = $this->autoReplyChatOverrides();
        $overrides[$chatId] = $enabled;

        $this->writeSetting(
            self::AUTO_REPLY_CHAT_OVERRIDES_KEY,
            json_encode($overrides, JSON_UNESCAPED_UNICODE)
        );

        return response()->json([
            'ok' => true,
            'chat_id' => $chatId,
            'enabled' => $enabled,
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $cfg    = $this->evoSettings();
        $chatId = $request->query('chat_id');

        if (! $cfg['url'] || ! $cfg['instance'] || ! $chatId) {
            return response()->json(['error' => 'Missing parameters.'], 422);
        }

        // Fetch page 1 (newest messages) then check if more pages exist
        $allRecords = collect();
        $page = 1;
        $maxPages = 3; // Fetch up to 150 messages (3 × 50)

        do {
            $response = $this->evoHttp($cfg)
                ->post("{$cfg['url']}/chat/findMessages/{$cfg['instance']}", [
                    'where' => ['key' => ['remoteJid' => $chatId]],
                    'limit' => 50,
                    'page'  => $page,
                ]);

            if (! $response->successful()) {
                Log::error('EVO findMessages failed', ['status' => $response->status(), 'body' => $response->body()]);
                break;
            }

            $body    = $response->json();
            $records = data_get($body, 'messages.records')
                ?? data_get($body, 'records')
                ?? (is_array($body) && isset($body[0]) ? $body : []);

            $allRecords = $allRecords->merge($records);

            $totalPages = data_get($body, 'messages.pages', 1);
            $page++;
        } while ($page <= min($totalPages, $maxPages));

        $messages = $allRecords
            ->values()
            ->map(fn($m, $idx) => [
                'id'        => data_get($m, 'key.id'),
                'from_me'   => $this->normalizeBool(data_get($m, 'key.fromMe', false)),
                'text'      => $this->extractMessageText((array) $m),
                'timestamp' => $this->normalizeMessageTimestamp($m['messageTimestamp'] ?? 0),
                '_idx'      => (int) $idx,
            ])
            ->filter(fn($m) => $m['id'] !== null && trim((string) $m['text']) !== '')
            ->unique('id')
            // Stable order: timestamp ASC, then original index ASC.
            ->sort(function ($a, $b) {
                $tsCmp = ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0);
                if ($tsCmp !== 0) {
                    return $tsCmp;
                }

                return ($a['_idx'] ?? 0) <=> ($b['_idx'] ?? 0);
            })
            ->map(function ($m) {
                unset($m['_idx']);

                return $m;
            })
            ->values();

        return response()->json($messages);
    }

    public function send(Request $request): JsonResponse
    {
        $cfg = $this->evoSettings();

        $validated = $request->validate([
            'chat_id' => ['required', 'string'],
            'text'    => ['required', 'string', 'max:4096'],
        ]);

        if (! $cfg['url'] || ! $cfg['instance']) {
            return response()->json(['error' => 'Evolution API not configured.'], 422);
        }

        // sendText expects a plain number (no @s.whatsapp.net)
        $number = $this->jidToNumber($validated['chat_id']);

        $response = $this->evoHttp($cfg)
            ->post("{$cfg['url']}/message/sendText/{$cfg['instance']}", [
                'number' => $number,
                'text'   => $validated['text'],
            ]);

        if (! $response->successful()) {
            Log::error('EVO sendText failed', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json([
                'error'   => 'Failed to send message.',
                'details' => $response->json() ?? $response->body(),
            ], $response->status());
        }

        return response()->json(['ok' => true, 'message' => $response->json()]);
    }

    /** Debug endpoint – returns raw API responses, useful for troubleshooting */
    public function debug(): JsonResponse
    {
        $cfg = $this->evoSettings();

        if (! $cfg['url'] || ! $cfg['instance']) {
            return response()->json(['error' => 'Not configured'], 422);
        }

        $statusRes = $this->evoHttp($cfg)
            ->get("{$cfg['url']}/instance/connectionState/{$cfg['instance']}");

        $chatsRes = $this->evoHttp($cfg)
            ->post("{$cfg['url']}/chat/findChats/{$cfg['instance']}", []);

        return response()->json([
            'config'  => ['url' => $cfg['url'], 'instance' => $cfg['instance'], 'key_set' => (bool) $cfg['key']],
            'status'  => ['code' => $statusRes->status(), 'body' => $statusRes->json()],
            'chats'   => ['code' => $chatsRes->status(), 'body_preview' => array_slice((array) $chatsRes->json(), 0, 2)],
        ]);
    }
}
