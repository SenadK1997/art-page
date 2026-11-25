<?php

namespace App\Filament\Resources\PaintingStyles\Tables;

use App\Models\PaintingStyle;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PaintingStylesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->paginated(false)
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->imageHeight(48),
                TextColumn::make('name')->label('Naziv')->weight('medium'),
                TextColumn::make('multiplier')
                    ->label('Cijena / 100 cm²')
                    ->formatStateUsing(fn (int $state) => $state.' KM'),
                TextColumn::make('example')
                    ->label('Primjer 50 × 70 cm')
                    ->state(fn (PaintingStyle $record) => $record->priceFor(50, 70).' KM'),
                TextColumn::make('limits')
                    ->label('Veličine po mjeri')
                    ->state(fn (PaintingStyle $record) => "{$record->min_width}×{$record->min_height} – {$record->max_width}×{$record->max_height} cm"),
                TextColumn::make('products_count')->label('Slika u galeriji')->counts('products'),
                ToggleColumn::make('is_active')->label('U kalkulatoru'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
