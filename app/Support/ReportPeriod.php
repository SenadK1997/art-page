<?php

namespace App\Support;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/** The date range chosen on the sales report page. */
class ReportPeriod
{
    public const OPTIONS = [
        'this_month' => 'Ovaj mjesec',
        'last_month' => 'Prošli mjesec',
        'last_30' => 'Zadnjih 30 dana',
        'this_year' => 'Ova godina',
        'last_year' => 'Prošla godina',
        'all' => 'Sve',
        'custom' => 'Odaberi datume',
    ];

    public function __construct(public CarbonImmutable $from, public CarbonImmutable $to) {}

    public static function fromFilters(?array $filters): self
    {
        $now = CarbonImmutable::now();

        return match ($filters['period'] ?? 'this_month') {
            'last_month' => new self($now->subMonthNoOverflow()->startOfMonth(), $now->subMonthNoOverflow()->endOfMonth()),
            'last_30' => new self($now->subDays(29)->startOfDay(), $now->endOfDay()),
            'this_year' => new self($now->startOfYear(), $now->endOfDay()),
            'last_year' => new self($now->subYear()->startOfYear(), $now->subYear()->endOfYear()),
            'all' => new self(
                CarbonImmutable::parse(Order::min('created_at') ?? $now)->startOfDay(),
                $now->endOfDay(),
            ),
            'custom' => new self(
                CarbonImmutable::parse($filters['from'] ?? $now->startOfMonth())->startOfDay(),
                CarbonImmutable::parse($filters['to'] ?? $now)->endOfDay(),
            ),
            default => new self($now->startOfMonth(), $now->endOfDay()),
        };
    }

    /** Orders in the period that count as sales (everything except cancelled). */
    public function orders(): Builder
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$this->from, $this->to]);
    }

    public function byMonth(): bool
    {
        return $this->from->diffInDays($this->to) > 62;
    }
}
