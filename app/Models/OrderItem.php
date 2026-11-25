<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'kind', 'customer_photo', 'customer_note', 'promotion_title', 'product_id', 'title', 'style_name', 'width', 'height',
        'frame', 'unit_price', 'original_unit_price', 'qty', 'from_stock',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'qty' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotal(): int
    {
        return $this->unit_price * $this->qty;
    }

    public function frameLabel(): ?string
    {
        return $this->frame ? (config('shop.frames')[$this->frame]['label'] ?? $this->frame) : null;
    }
}
