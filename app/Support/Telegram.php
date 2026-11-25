<?php

namespace App\Support;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends new-order alerts to the artist's Telegram. He creates a bot with
 * @BotFather, pastes the token in Admin → Postavke, sends the bot a message,
 * and the admin finds his chat id automatically.
 */
class Telegram
{
    public function __construct(private Settings $settings) {}

    public function isConfigured(): bool
    {
        return $this->settings->bool('telegram_enabled')
            && filled($this->settings->get('telegram_bot_token'))
            && filled($this->settings->get('telegram_chat_id'));
    }

    public function notifyNewOrder(Order $order): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $lines = $order->items->map(function ($i) {
            $tag = match ($i->kind) {
                'commission' => $i->customer_photo ? '🖼 Po fotografiji' : '🎨 Po mjeri',
                'gift' => '🎁 Poklon',
                default => $i->from_stock >= $i->qty ? '📦 Sa stanja' : '🖌 Naslikati',
            };
            $price = $i->kind === 'gift' ? 'gratis' : number_format($i->lineTotal(), 0, ',', '.').' KM';

            return "• {$tag}: <b>".e($i->title).'</b>'.($i->style_name ? ' ('.e($i->style_name).')' : '')
                ."\n   {$i->width}×{$i->height} cm".($i->frameLabel() ? ', ram '.e($i->frameLabel()) : '')
                .($i->qty > 1 ? ", {$i->qty} kom" : '')." — {$price}";
        })->implode("\n");

        $text = "🛒 <b>Nova narudžba {$order->number}</b>\n\n"
            .'👤 '.e($order->full_name)."\n"
            .'📞 '.e($order->phone)."\n"
            .'📍 '.e($order->address).', '.e(trim($order->zip.' '.$order->city))."\n\n"
            .$lines."\n\n"
            .($order->shipping ? 'Dostava: '.$order->shipping." KM\n" : '')
            .'<b>Ukupno: '.number_format($order->total, 0, ',', '.').' KM</b> (pouzećem)'
            .($order->note ? "\n\n💬 ".e($order->note) : '');

        $this->send($text, OrderResource::getUrl('edit', ['record' => $order]));
    }

    /** @return array{ok: bool, error: ?string} */
    public function send(string $html, ?string $buttonUrl = null, ?string $token = null, ?string $chatId = null): array
    {
        $token ??= $this->settings->get('telegram_bot_token');
        $chatId ??= $this->settings->get('telegram_chat_id');

        $payload = [
            'chat_id' => $chatId,
            'text' => $html,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        // Telegram only accepts public https links on buttons.
        if ($buttonUrl && str_starts_with($buttonUrl, 'https://')) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => [[['text' => 'Otvori narudžbu', 'url' => $buttonUrl]]]]);
        }

        try {
            $response = Http::timeout(8)->asForm()->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
            if ($response->json('ok')) {
                return ['ok' => true, 'error' => null];
            }
            $error = $response->json('description') ?? 'HTTP '.$response->status();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }
        Log::warning('Telegram send failed', ['error' => $error]);

        return ['ok' => false, 'error' => $error];
    }

    /**
     * Chats that recently wrote to the bot, newest first, so the artist
     * can pick his own instead of hunting for a chat id.
     *
     * @return array<string, string> chat id => name
     */
    public function recentChats(string $token): array
    {
        $response = Http::timeout(8)->get("https://api.telegram.org/bot{$token}/getUpdates");
        if (! $response->json('ok')) {
            throw new \RuntimeException($response->json('description') ?? 'Token nije ispravan.');
        }

        return collect($response->json('result', []))
            ->map(fn ($u) => $u['message']['chat'] ?? $u['my_chat_member']['chat'] ?? $u['channel_post']['chat'] ?? null)
            ->filter()
            ->reverse()
            ->mapWithKeys(fn ($chat) => [(string) $chat['id'] => trim(($chat['title'] ?? '').' '.($chat['first_name'] ?? '').' '.($chat['last_name'] ?? '').(isset($chat['username']) ? ' (@'.$chat['username'].')' : ''))])
            ->all();
    }
}
