<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('items')->with('items'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label('Broj')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (Order $record) => $record->created_at->format('d.m.Y. H:i')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => config('shop.order_statuses')[$state] ?? $state)
                    ->color(fn (string $state) => OrderForm::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('full_name')
                    ->label('Kupac')
                    ->searchable()
                    ->description(fn (Order $record) => $record->phone),
                TextColumn::make('city')->label('Grad')->searchable()->toggleable(),
                TextColumn::make('items')
                    ->label('Slike')
                    ->state(fn (Order $record) => $record->items
                        ->map(fn ($i) => ($i->kind === 'commission' ? 'Po mjeri: ' : '').$i->title.' '.$i->width.'×'.$i->height.($i->qty > 1 ? ' ×'.$i->qty : ''))
                        ->all())
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),
                TextColumn::make('total')
                    ->label('Ukupno')
                    ->formatStateUsing(fn (int $state) => number_format($state, 0, ',', '.').' KM')
                    ->description(fn (Order $record) => $record->wasAdjusted() ? 'naručeno '.number_format($record->original_total, 0, ',', '.').' KM' : null)
                    ->alignEnd()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(config('shop.order_statuses')),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('Potvrdi')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'new')
                    ->action(fn (Order $record) => $record->update(['status' => 'confirmed'])),
                EditAction::make()->label('Otvori'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Još nema narudžbi')
            ->emptyStateDescription('Kada kupac naruči sliku, pojavit će se ovdje i dobit ćete email.');
    }
}
