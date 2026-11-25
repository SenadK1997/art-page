<?php

namespace App\Filament\Widgets\Report;

use App\Models\OrderItem;
use App\Support\ReportPeriod;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $period = ReportPeriod::fromFilters($this->pageFilters);
        $orders = $period->orders();
        $count = (clone $orders)->count();
        $revenue = (int) (clone $orders)->sum('total');
        $paid = (int) (clone $orders)->where('status', 'completed')->sum('total');
        $open = (int) (clone $orders)->whereNotIn('status', ['completed'])->sum('total');

        $items = OrderItem::whereIn('order_id', (clone $orders)->select('id'))->get();
        $sold = (int) $items->where('kind', '!=', 'gift')->sum('qty');
        $saleSavings = (int) $items->whereNotNull('original_unit_price')
            ->sum(fn ($i) => ($i->original_unit_price - $i->unit_price) * $i->qty);
        $manualDiscounts = (int) (clone $orders)->sum('discount');

        $km = fn (int $v) => number_format($v, 0, ',', '.').' KM';

        return [
            Stat::make('Prihod', $km($revenue))
                ->description("{$count} narudžbi · bez otkazanih"),
            Stat::make('Naplaćeno', $km($paid))
                ->description('Još za naplatu: '.$km($open))
                ->color('success'),
            Stat::make('Prosječna narudžba', $km($count ? (int) round($revenue / $count) : 0))
                ->description("{$sold} slika prodano"),
            Stat::make('Popusti i pokloni', $km($saleSavings + $manualDiscounts))
                ->description('Akcije i pokloni '.$km($saleSavings).' · dogovoreni popusti '.$km($manualDiscounts))
                ->color('warning'),
        ];
    }
}
