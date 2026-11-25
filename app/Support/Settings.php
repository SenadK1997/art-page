<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Small key/value store the artist edits in Admin → Postavke.
 * Secret values (the Telegram bot token) are stored encrypted.
 */
class Settings
{
    private const SECRET = ['telegram_bot_token'];

    private const CACHE_KEY = 'shop.settings';

    public const DEFAULTS = [
        'owner_email' => null,
        'phone' => null,
        'shipping_fee' => null,          // KM; empty = "according to the courier's price list"
        'free_shipping_over' => null,    // KM; empty = never free
        'made_to_order_time' => 'izrada 2–3 sedmice',
        'telegram_enabled' => '0',
        'telegram_bot_token' => null,
        'telegram_chat_id' => null,
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        $value = $all[$key] ?? null;

        return $value === null || $value === '' ? ($default ?? self::DEFAULTS[$key] ?? null) : $value;
    }

    public function int(string $key): ?int
    {
        $value = $this->get($key);

        return is_numeric($value) ? (int) $value : null;
    }

    public function bool(string $key): bool
    {
        return (bool) $this->get($key);
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                $rows = DB::table('settings')->pluck('value', 'key')->all();
            } catch (\Throwable) {
                return [];
            }
            foreach (self::SECRET as $key) {
                if (! empty($rows[$key])) {
                    try {
                        $rows[$key] = Crypt::decryptString($rows[$key]);
                    } catch (\Throwable) {
                        $rows[$key] = null;
                    }
                }
            }

            return $rows;
        });
    }

    /** @param array<string, mixed> $values */
    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            if (in_array($key, self::SECRET, true) && filled($value)) {
                $value = Crypt::encryptString((string) $value);
            }
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    /** Delivery price for a cart subtotal, or null when it is "by the courier's price list". */
    public function shippingFor(int $subtotal): ?int
    {
        $fee = $this->int('shipping_fee');
        if ($fee === null) {
            return null;
        }
        $freeOver = $this->int('free_shipping_over');

        return $freeOver !== null && $subtotal >= $freeOver ? 0 : $fee;
    }
}
