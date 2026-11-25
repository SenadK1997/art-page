<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'number', 'status', 'full_name', 'phone', 'email', 'address', 'city', 'zip',
        'country', 'note', 'payment_method', 'subtotal', 'discount', 'shipping', 'total',
        'original_total', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount' => 'integer',
            'shipping' => 'integer',
            'total' => 'integer',
            'original_total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number ??= 'FA-'.now()->format('ymd').'-'.Str::upper(Str::random(4));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Recompute subtotal and total from the items, discount and delivery. */
    public function recalculate(): static
    {
        $this->subtotal = (int) $this->items()->get()->sum(fn (OrderItem $i) => $i->lineTotal());
        $this->total = max(0, $this->subtotal - (int) $this->discount + (int) $this->shipping);

        return $this;
    }

    public function wasAdjusted(): bool
    {
        return $this->original_total !== null && $this->original_total !== $this->total;
    }

    public function statusLabel(): string
    {
        return config('shop.order_statuses')[$this->status] ?? $this->status;
    }
}
