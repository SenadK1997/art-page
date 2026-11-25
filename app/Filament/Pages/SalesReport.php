<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Report\RevenueChart;
use App\Filament\Widgets\Report\SalesBreakdown;
use App\Filament\Widgets\Report\SalesStats;
use App\Filament\Widgets\Report\TopPaintings;
use App\Support\ReportPeriod;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SalesReport extends Dashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'izvjestaj';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Izvještaj prodaje';

    protected static ?string $title = 'Izvještaj prodaje';

    protected static ?int $navigationSort = 2;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    Select::make('period')
                        ->label('Period')
                        ->options(ReportPeriod::OPTIONS)
                        ->default('this_month')
                        ->selectablePlaceholder(false),
                    DatePicker::make('from')->label('Od')->native(false)->displayFormat('d.m.Y.')
                        ->visible(fn (Get $get) => $get('period') === 'custom'),
                    DatePicker::make('to')->label('Do')->native(false)->displayFormat('d.m.Y.')
                        ->visible(fn (Get $get) => $get('period') === 'custom'),
                ]),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            SalesStats::class,
            RevenueChart::class,
            SalesBreakdown::class,
            TopPaintings::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
