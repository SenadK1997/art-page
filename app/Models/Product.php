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
        'title', 'slug', 'description', 'image', 'room_images', 'gallery', 'technique', 'year',
        'painting_style_id', 'is_featured', 'is_available', 'made_to_order',
        'discount_percent', 'discount_ends_at', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'room_images' => 'array',
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

    /** Has at least one photo (of the painting or in a room). */
    public function scopeWithPhoto(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNotNull('image')->orWhereNotNull('room_images'));
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

    /** The plain photo of the painting itself (no room), used for the wall preview. */
    public function artworkUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function hasArtwork(): bool
    {
        return filled($this->image);
    }

    /** Cover for cards and lists: the painting photo, else the first room photo. */
    public function imageUrl(): ?string
    {
        $path = $this->image ?: ($this->room_images[0] ?? null);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /** The cover is a room photo, so it must not get a second frame. */
    public function coverIsRoomPhoto(): bool
    {
        return ! $this->hasArtwork() && filled($this->room_images);
    }

    /**
     * Every photo for the product page, in order: painting, room photos, details.
     * `framed` is true only for the plain painting photo.
     *
     * @return list<array{url: string, framed: bool}>
     */
    public function photos(): array
    {
        $url = fn ($path) => Storage::disk('public')->url($path);

        return collect()
            ->when($this->image, fn ($c) => $c->push(['url' => $url($this->image), 'framed' => true]))
            ->concat(collect($this->room_images ?? [])->filter()->map(fn ($p) => ['url' => $url($p), 'framed' => false]))
            ->concat(collect($this->gallery ?? [])->filter()->map(fn ($p) => ['url' => $url($p), 'framed' => false]))
            ->values()
            ->all();
    }

    /** @return list<string> */
    public function galleryUrls(): array
    {
        return array_column($this->photos(), 'url');
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
