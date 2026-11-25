<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShopOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $new = Order::where('status', 'new')->count();
        $month = Order::where('status', '!=', 'cancelled')->where('created_at', '>=', now()->startOfMonth());

        return [
            Stat::make('Nove narudžbe', $new)
                ->description($new ? 'Čekaju vaš poziv kupcu' : 'Sve je obrađeno')
                ->color($new ? 'warning' : 'success')
                ->url(OrderResource::getUrl('index', ['filters' => ['status' => ['value' => 'new']]])),
            Stat::make('Narudžbe ovog mjeseca', (clone $month)->count())
                ->description(number_format((clone $month)->sum('total'), 0, ',', '.').' KM ukupno'),
            Stat::make('Slike u galeriji', Product::visible()->count())
                ->description('Dodaj novu sliku')
                ->url(ProductResource::getUrl('create')),
        ];
    }
}
