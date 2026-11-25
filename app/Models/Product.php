<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'image', 'gallery', 'technique', 'year',
        'painting_style_id', 'is_featured', 'is_available', 'made_to_order',
        'discount_percent', 'discount_ends_at', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'made_to_order' => 'boolean',
            'discount_percent' => 'integer',
            'discount_ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $base = Str::slug($product->title) ?: 'slika';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->whereKeyNot($product->getKey())->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $product->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(PaintingStyle::class, 'painting_style_id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('sort')->orderBy('price');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('is_available', true)->whereHas('sizes');
    }

    public function scopeOnSale(Builder $query): void
    {
        $query->where('discount_percent', '>', 0)
            ->where(fn ($q) => $q->whereNull('discount_ends_at')->orWhere('discount_ends_at', '>', now()));
    }

    public function scopeInStock(Builder $query): void
    {
        $query->whereHas('sizes', fn ($q) => $q->where('stock', '>', 0));
    }

    public function activeDiscount(): int
    {
        if ($this->discount_percent <= 0) {
            return 0;
        }
        if ($this->discount_ends_at && $this->discount_ends_at->isPast()) {
            return 0;
        }

        return min($this->discount_percent, 90);
    }

    public function applyDiscount(int $price): int
    {
        $d = $this->activeDiscount();

        return $d ? (int) round($price * (100 - $d) / 100) : $price;
    }

    public function isInStock(): bool
    {
        return $this->sizes->contains(fn (ProductSize $s) => $s->stock > 0);
    }

    /** Sold out: a one-off piece with nothing left in stock. */
    public function isSoldOut(): bool
    {
        return ! $this->made_to_order && ! $this->isInStock();
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /** @return list<string> */
    public function galleryUrls(): array
    {
        return collect([$this->image, ...($this->gallery ?? [])])
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    /** Lowest regular price across sizes. */
    public function startingPrice(): ?int
    {
        return $this->sizes->min('price');
    }

    /** Lowest price a buyer pays today. */
    public function startingFinalPrice(): ?int
    {
        $min = $this->startingPrice();

        return $min === null ? null : $this->applyDiscount($min);
    }
}
