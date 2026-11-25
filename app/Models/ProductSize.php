<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSize extends Model
{
    protected $fillable = ['product_id', 'width', 'height', 'price', 'stock', 'sort'];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function label(): string
    {
        return $this->width.' × '.$this->height.' cm';
    }

    public function finalPrice(): int
    {
        return $this->product->applyDiscount($this->price);
    }

    /** Can be bought now: in stock, or the artist paints it to order. */
    public function isPurchasable(): bool
    {
        return $this->stock > 0 || $this->product->made_to_order;
    }

    /** Most a single order can take of this size. */
    public function maxQty(): int
    {
        return $this->product->made_to_order ? 10 : min($this->stock, 10);
    }
}
