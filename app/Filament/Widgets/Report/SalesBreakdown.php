<?php

namespace App\Filament\Widgets\Report;

use App\Models\OrderItem;
use App\Support\ReportPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesBreakdown extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Prihod po stilu';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $period = ReportPeriod::fromFilters($this->pageFilters);

        $byStyle = OrderItem::whereIn('order_id', $period->orders()->select('id'))
            ->where('kind', '!=', 'gift')
            ->get()
            ->groupBy(fn ($i) => $i->style_name ?: ($i->kind === 'extra' ? 'Dodatno' : 'Bez stila'))
            ->map(fn ($items) => $items->sum(fn ($i) => $i->lineTotal()))
            ->sortDesc();

        return [
            'datasets' => [[
                'data' => $byStyle->values()->all(),
                'backgroundColor' => ['#b88c45', '#1c1a17', '#8a8073', '#e2be7e', '#5d554b', '#c9bca8'],
            ]],
            'labels' => $byStyle->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
