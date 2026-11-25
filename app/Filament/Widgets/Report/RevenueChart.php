<?php

namespace App\Filament\Widgets\Report;

use App\Support\ReportPeriod;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Prihod kroz vrijeme';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $period = ReportPeriod::fromFilters($this->pageFilters);
        $monthly = $period->byMonth();
        $key = fn ($date) => $monthly ? $date->format('Y-m') : $date->format('Y-m-d');

        $totals = $period->orders()->get(['created_at', 'total'])
            ->groupBy(fn ($o) => $key($o->created_at))
            ->map->sum('total');

        $labels = [];
        $values = [];
        $step = CarbonPeriod::create($period->from, $monthly ? '1 month' : '1 day', $period->to);
        foreach ($step as $date) {
            $labels[] = $monthly ? $date->format('m.Y.') : $date->format('d.m.');
            $values[] = (int) ($totals[$key($date)] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Prihod (KM)',
                'data' => $values,
                'borderColor' => '#b88c45',
                'backgroundColor' => 'rgba(184, 140, 69, 0.15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
