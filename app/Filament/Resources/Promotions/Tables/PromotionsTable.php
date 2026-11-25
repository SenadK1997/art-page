<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Models\Promotion;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('requiredProducts', 'gifts.product'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('Naziv')->weight('medium')
                    ->wrap()
                    ->description(fn (Promotion $record) => $record->conditionText().($record->ends_at ? ' · do '.$record->ends_at->format('d.m.') : '').($record->is_active ? '' : ' · ugašena')),
                TextColumn::make('gifts_list')
                    ->label('Pokloni')
                    ->state(fn (Promotion $record) => $record->gifts->map(fn ($g) => $g->product->title.' '.$g->label())->all())
                    ->listWithLineBreaks()->limitList(2)->expandableLimitedList()->visibleFrom('md'),
                TextColumn::make('period')
                    ->label('Traje')
                    ->state(fn (Promotion $record) => ($record->starts_at?->format('d.m.Y.') ?? 'odmah').' – '.($record->ends_at?->format('d.m.Y.') ?? 'dok ne ugasite'))
                    ->visibleFrom('lg'),
                ToggleColumn::make('is_active')->label('Aktivna')->visibleFrom('md'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Nema poklon akcija')
            ->emptyStateDescription('Npr. „Kupite dvije slike i izaberite treću manju na poklon“.');
    }
}
