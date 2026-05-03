<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
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

        $chats = collect((array) $raw)
            ->filter(fn($c) => isset($c['remoteJid']))
            ->map(fn($c) => [
                'id'           => $c['remoteJid'],
                'name'         => $c['pushName']
                    ?? data_get($c, 'lastMessage.pushName')
                    ?? $this->jidToNumber($c['remoteJid']),
                'last_message' => data_get($c, 'lastMessage.message.conversation')
                    ?? data_get($c, 'lastMessage.message.extendedTextMessage.text')
                    ?? '...',
                'timestamp'    => data_get($c, 'lastMessage.messageTimestamp', 0),
                'unread'       => $c['unreadCount'] ?? 0,
            ])
            ->sortByDesc('timestamp')
            ->values();

        return response()->json($chats);
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
            ->map(fn($m) => [
                'id'        => data_get($m, 'key.id'),
                'from_me'   => (bool) data_get($m, 'key.fromMe', false),
                'text'      => data_get($m, 'message.conversation')
                    ?? data_get($m, 'message.extendedTextMessage.text')
                    ?? data_get($m, 'message.imageMessage.caption')
                    ?? '[media]',
                'timestamp' => (int) ($m['messageTimestamp'] ?? 0),
            ])
            ->filter(fn($m) => $m['id'] !== null && $m['text'] !== '[media]')
            ->unique('id')
            ->sortBy('timestamp')
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
