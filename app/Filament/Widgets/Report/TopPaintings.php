<?php

namespace App\Filament\Widgets\Report;

use App\Models\OrderItem;
use App\Support\ReportPeriod;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class TopPaintings extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.top-paintings';

    protected function getViewData(): array
    {
        $period = ReportPeriod::fromFilters($this->pageFilters);

        $items = OrderItem::whereIn('order_id', $period->orders()->select('id'))->get();

        $rows = $items->where('kind', '!=', 'gift')
            ->groupBy(fn ($i) => $i->kind === 'commission' ? 'Po mjeri · '.$i->style_name : $i->title)
            ->map(fn ($group, $name) => [
                'name' => $name,
                'qty' => $group->sum('qty'),
                'revenue' => $group->sum(fn ($i) => $i->lineTotal()),
            ])
            ->sortByDesc('revenue')->take(8)->values();

        return [
            'rows' => $rows,
            'gifts' => $items->where('kind', 'gift')->count(),
            'fromStock' => $items->sum('from_stock'),
        ];
    }
}
