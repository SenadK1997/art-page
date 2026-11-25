<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * "Buy these (or spend / buy this many) and pick a free painting."
 * Every condition that is filled in must be met.
 */
class Promotion extends Model
{
    protected $fillable = ['title', 'description', 'is_active', 'starts_at', 'ends_at', 'min_total', 'min_paintings'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'min_total' => 'integer',
            'min_paintings' => 'integer',
        ];
    }

    public function requiredProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_required_product');
    }

    public function gifts(): BelongsToMany
    {
        return $this->belongsToMany(ProductSize::class, 'promotion_gift');
    }

    public function scopeRunning(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $paidLines  cart lines without gifts
     */
    public function isMetBy(Collection $paidLines): bool
    {
        if ($this->min_total && $paidLines->sum('total') < $this->min_total) {
            return false;
        }
        if ($this->min_paintings && $paidLines->sum('qty') < $this->min_paintings) {
            return false;
        }
        $required = $this->requiredProducts->pluck('id');
        if ($required->isNotEmpty() && $required->diff($paidLines->pluck('product_id')->filter())->isNotEmpty()) {
            return false;
        }

        return true;
    }

    /** Gift sizes that can actually be given right now. */
    public function availableGifts(): Collection
    {
        return $this->gifts->filter(fn (ProductSize $s) => $s->product?->is_available && $s->isPurchasable())->values();
    }

    /** Human summary of the conditions, for banners. */
    public function conditionText(): string
    {
        $parts = [];
        if ($this->requiredProducts->isNotEmpty()) {
            $parts[] = 'kupite '.$this->requiredProducts->pluck('title')->map(fn ($t) => "„{$t}“")->join(', ', ' i ');
        }
        if ($this->min_paintings) {
            $parts[] = 'kupite '.$this->min_paintings.' ili više slika';
        }
        if ($this->min_total) {
            $parts[] = 'naručite iznad '.number_format($this->min_total, 0, ',', '.').' KM';
        }

        return $parts ? ucfirst(implode(' i ', $parts)) : 'Uz svaku narudžbu';
    }
}
