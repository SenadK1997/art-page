<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('sizes', 'style'))
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(64)
                    ->imageWidth(52),
                TextColumn::make('title')
                    ->label('Naziv')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (Product $record) => $record->style?->name),
                TextColumn::make('sizes')
                    ->label('Veličine i cijene')
                    ->state(fn (Product $record) => $record->sizes
                        ->map(fn ($s) => "{$s->width}×{$s->height} cm — "
                            .($record->activeDiscount() ? $record->applyDiscount($s->price)." KM (bilo {$s->price})" : "{$s->price} KM")
                            .($s->stock > 0 ? " · na stanju: {$s->stock}" : ''))
                        ->all())
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (Product $record) => array_values(array_filter([
                        $record->isSoldOut() ? 'Prodano' : null,
                        $record->activeDiscount() ? 'Akcija −'.$record->activeDiscount().'%' : null,
                        $record->isInStock() ? 'Na stanju' : null,
                    ])))
                    ->badge()
                    ->color(fn (string $state) => match (true) {
                        $state === 'Prodano' => 'gray',
                        str_starts_with($state, 'Akcija') => 'warning',
                        default => 'success',
                    }),
                ToggleColumn::make('is_available')->label('U galeriji'),
                ToggleColumn::make('is_featured')->label('Na naslovnoj'),
                TextColumn::make('updated_at')
                    ->label('Izmijenjeno')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('painting_style_id')->label('Stil')->relationship('style', 'name'),
                TernaryFilter::make('is_available')->label('U galeriji'),
                Filter::make('on_sale')->label('Na akciji')->query(fn ($query) => $query->onSale()),
                Filter::make('in_stock')->label('Na stanju')->query(fn ($query) => $query->inStock()),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Pogledaj')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn (Product $record) => route('gallery.show', $record), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Još nema slika')
            ->emptyStateDescription('Dodajte prvu sliku dugmetom gore desno.');
    }
}
