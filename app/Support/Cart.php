<?php

namespace App\Support;

use App\Models\PaintingStyle;
use App\Models\ProductSize;
use App\Models\Promotion;
use Illuminate\Support\Collection;

/**
 * Session cart. It stores only what the buyer chose (size id, or style + cm,
 * an optional photo, a chosen gift), never a price: prices are always
 * recomputed from the database.
 */
class Cart
{
    private const KEY = 'cart.lines';

    private const GIFTS = 'cart.gifts';

    public function __construct(private Settings $settings) {}

    public function addPainting(int $sizeId, ?string $frame, int $qty = 1): void
    {
        $frame = $this->validFrame($frame);

        $this->put("p{$sizeId}-{$frame}", [
            'type' => 'painting',
            'size_id' => $sizeId,
            'frame' => $frame,
        ], $qty);
    }

    /** $photo is a path on the private `local` disk, from the customer's upload. */
    public function addCommission(int $styleId, int $width, int $height, ?string $frame, int $qty = 1, ?string $photo = null): void
    {
        $frame = $this->validFrame($frame);
        $key = "c{$styleId}-{$width}x{$height}-{$frame}".($photo ? '-'.substr(md5($photo), 0, 8) : '');

        $this->put($key, [
            'type' => 'commission',
            'style_id' => $styleId,
            'width' => $width,
            'height' => $height,
            'frame' => $frame,
            'photo' => $photo,
        ], $qty);
    }

    public function setQty(string $key, int $qty): void
    {
        $lines = $this->raw();
        if (! isset($lines[$key])) {
            return;
        }
        if ($qty < 1) {
            unset($lines[$key]);
        } else {
            $lines[$key]['qty'] = min($qty, 10);
        }
        session()->put(self::KEY, $lines);
    }

    public function remove(string $key): void
    {
        $this->setQty($key, 0);
    }

    public function clear(): void
    {
        session()->forget([self::KEY, self::GIFTS]);
    }

    public function chooseGift(int $promotionId, ?int $sizeId): void
    {
        $gifts = session()->get(self::GIFTS, []);
        if ($sizeId) {
            $gifts[$promotionId] = $sizeId;
        } else {
            unset($gifts[$promotionId]);
        }
        session()->put(self::GIFTS, $gifts);
    }

    public function count(): int
    {
        return (int) $this->lines()->sum('qty');
    }

    public function isEmpty(): bool
    {
        return $this->paidLines()->isEmpty();
    }

    /** Path of a customer photo in this cart, if the key belongs to this session. */
    public function photoFor(string $key): ?string
    {
        return $this->raw()[$key]['photo'] ?? null;
    }

    /**
     * Everything in the cart: paid lines, then chosen gifts.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function lines(): Collection
    {
        $paid = $this->paidLines();
        $gifts = $this->promotions()
            ->filter(fn ($p) => $p['eligible'] && $p['chosen'])
            ->map(fn ($p) => $this->giftLine($p['promotion'], $p['chosen']));

        return $paid->concat($gifts)->values();
    }

    /** @return array{subtotal: int, shipping: ?int, total: int, savings: int} */
    public function summary(): array
    {
        $lines = $this->lines();
        $subtotal = (int) $lines->sum('total');
        $shipping = $lines->isEmpty() ? null : $this->settings->shippingFor($subtotal);

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + ($shipping ?? 0),
            'savings' => (int) $lines->sum(fn ($l) => $l['original_unit_price'] ? ($l['original_unit_price'] - $l['unit_price']) * $l['qty'] : 0),
        ];
    }

    /**
     * Running gift promotions with whether this cart qualifies and which gift is picked.
     *
     * @return Collection<int, array{promotion: Promotion, eligible: bool, gifts: Collection, chosen: ?ProductSize}>
     */
    public function promotions(): Collection
    {
        $paid = $this->paidLines();
        $chosen = session()->get(self::GIFTS, []);

        return Promotion::running()->with('requiredProducts', 'gifts.product')->get()
            ->map(function (Promotion $promotion) use ($paid, $chosen) {
                $gifts = $promotion->availableGifts();
                $eligible = $paid->isNotEmpty() && $gifts->isNotEmpty() && $promotion->isMetBy($paid);

                return [
                    'promotion' => $promotion,
                    'eligible' => $eligible,
                    'gifts' => $gifts,
                    'chosen' => $eligible ? $gifts->firstWhere('id', $chosen[$promotion->id] ?? null) : null,
                ];
            })
            ->filter(fn ($p) => $p['gifts']->isNotEmpty())
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function paidLines(): Collection
    {
        return $this->resolvePaid();
    }

    private function resolvePaid(): Collection
    {
        $raw = $this->raw();

        $sizes = ProductSize::with('product.style')
            ->whereIn('id', collect($raw)->where('type', 'painting')->pluck('size_id'))
            ->get()->keyBy('id');

        $styles = PaintingStyle::whereIn('id', collect($raw)->where('type', 'commission')->pluck('style_id'))
            ->get()->keyBy('id');

        $frames = config('shop.frames');

        return collect($raw)->map(function (array $line, string $key) use ($sizes, $styles, $frames) {
            $frame = $line['frame'] ?? 'none';
            $base = [
                'key' => $key,
                'type' => $line['type'],
                'qty' => $line['qty'],
                'frame' => $frame,
                'frame_label' => $frames[$frame]['label'] ?? null,
                'photo' => null,
                'promotion_title' => null,
            ];

            if ($line['type'] === 'painting') {
                $size = $sizes->get($line['size_id']);
                if (! $size || ! $size->product || ! $size->product->is_available || ! $size->isPurchasable()) {
                    return null;
                }
                $qty = min($line['qty'], $size->maxQty());
                $price = $size->finalPrice();

                return ['qty' => $qty] + $base + [
                    'size_id' => $size->id,
                    'product_id' => $size->product_id,
                    'title' => $size->product->title,
                    'style_name' => $size->product->style?->name,
                    'image' => $size->product->imageUrl(),
                    'url' => route('gallery.show', $size->product),
                    'width' => $size->width,
                    'height' => $size->height,
                    'unit_price' => $price,
                    'original_unit_price' => $price < $size->price ? $size->price : null,
                    'in_stock' => $size->stock,
                    'max_qty' => $size->maxQty(),
                    'total' => $price * $qty,
                ];
            }

            $style = $styles->get($line['style_id']);
            if (! $style || ! $style->is_active || ! $style->acceptsSize($line['width'], $line['height'])) {
                return null;
            }
            $price = $style->priceFor($line['width'], $line['height']);
            $photo = $line['photo'] ?? null;

            return ['photo' => $photo] + $base + [
                'size_id' => null,
                'original_unit_price' => null,
                'in_stock' => 0,
                'max_qty' => 10,
                'product_id' => null,
                'title' => $photo ? 'Slika po vašoj fotografiji' : 'Slika po narudžbi',
                'style_name' => $style->name,
                'image' => $photo ? route('cart.photo', $key) : $style->imageUrl(),
                'url' => route('commission'),
                'width' => $line['width'],
                'height' => $line['height'],
                'unit_price' => $price,
                'total' => $price * $line['qty'],
            ];
        })->filter()->values();
    }

    private function giftLine(Promotion $promotion, ProductSize $size): array
    {
        return [
            'key' => 'gift-'.$promotion->id,
            'type' => 'gift',
            'qty' => 1,
            'frame' => 'none',
            'frame_label' => config('shop.frames.none.label'),
            'photo' => null,
            'promotion_title' => $promotion->title,
            'size_id' => $size->id,
            'product_id' => $size->product_id,
            'title' => $size->product->title,
            'style_name' => $size->product->style?->name,
            'image' => $size->product->imageUrl(),
            'url' => route('gallery.show', $size->product),
            'width' => $size->width,
            'height' => $size->height,
            'unit_price' => 0,
            'original_unit_price' => $size->price,
            'in_stock' => $size->stock,
            'max_qty' => 1,
            'total' => 0,
        ];
    }

    private function put(string $key, array $line, int $qty): void
    {
        $lines = $this->raw();
        $current = $lines[$key]['qty'] ?? 0;
        $lines[$key] = $line + ['qty' => min(max($current + $qty, 1), 10)];
        session()->put(self::KEY, $lines);
    }

    private function raw(): array
    {
        return session()->get(self::KEY, []);
    }

    private function validFrame(?string $frame): string
    {
        return array_key_exists((string) $frame, config('shop.frames')) ? $frame : 'none';
    }
}
