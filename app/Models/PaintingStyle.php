<?php

namespace App\Models;

use App\Support\PriceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PaintingStyle extends Model
{
    protected $fillable = [
        'name', 'slug', 'multiplier', 'description', 'image', 'sort', 'is_active',
        'min_width', 'min_height', 'max_width', 'max_height', 'allows_photo',
    ];

    protected function casts(): array
    {
        return [
            'multiplier' => 'integer',
            'min_width' => 'integer',
            'min_height' => 'integer',
            'max_width' => 'integer',
            'max_height' => 'integer',
            'allows_photo' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function acceptsSize(int $width, int $height): bool
    {
        return $width >= $this->min_width && $width <= $this->max_width
            && $height >= $this->min_height && $height <= $this->max_height;
    }

    public function priceFor(int $width, int $height): int
    {
        return PriceCalculator::price($width, $height, $this->multiplier);
    }

    /** The style's sample image, or else one of its own paintings so it is never blank. */
    public function imageUrl(): ?string
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        $painting = $this->products()->visible()->whereNotNull('image')
            ->orderByDesc('is_featured')->orderBy('sort')->first();

        return $painting?->imageUrl();
    }
}
