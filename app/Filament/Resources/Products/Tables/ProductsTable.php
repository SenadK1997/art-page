<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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
                    ->wrap()
                    // On phones the status columns are hidden, so the key facts sit under the name.
                    ->description(fn (Product $record) => collect([
                        $record->style?->name,
                        $record->startingFinalPrice() ? 'od '.$record->startingFinalPrice().' KM' : null,
                        $record->isSoldOut() ? 'Prodano' : null,
                        $record->activeDiscount() ? 'Akcija −'.$record->activeDiscount().'%' : null,
                        $record->is_featured ? '★ Naslovna' : null,
                    ])->filter()->join(' · ')),
                TextColumn::make('sizes')
                    ->label('Veličine i cijene')
                    ->state(fn (Product $record) => $record->sizes
                        ->map(fn ($s) => "{$s->width}×{$s->height} cm — "
                            .($record->activeDiscount() ? $record->applyDiscount($s->price)." KM (bilo {$s->price})" : "{$s->price} KM")
                            .($s->stock > 0 ? " · na stanju: {$s->stock}" : ''))
                        ->all())
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->visibleFrom('md'),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (Product $record) => array_values(array_filter([
                        $record->isSoldOut() ? 'Prodano' : null,
                        $record->activeDiscount() ? 'Akcija −'.$record->activeDiscount().'%' : null,
                        $record->isInStock() ? 'Na stanju' : null,
                        $record->is_featured ? 'Na naslovnoj' : null,
                    ])))
                    ->badge()
                    ->color(fn (string $state) => match (true) {
                        $state === 'Prodano' => 'gray',
                        str_starts_with($state, 'Akcija') => 'warning',
                        $state === 'Na naslovnoj' => 'info',
                        default => 'success',
                    })
                    ->visibleFrom('md'),
                ToggleColumn::make('is_available')->label('U galeriji')->visibleFrom('md'),
                ToggleColumn::make('is_featured')->label('Na naslovnoj')->visibleFrom('md'),
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
                EditAction::make()->label('Uredi'),
                ActionGroup::make([
                    self::saleAction(Action::make('sale')),
                    Action::make('endSale')
                        ->label('Završi akciju')
                        ->icon(Heroicon::OutlinedXMark)
                        ->visible(fn (Product $record) => $record->activeDiscount() > 0)
                        ->action(fn (Product $record) => $record->update(['discount_percent' => 0, 'discount_ends_at' => null])),
                    Action::make('view')
                        ->label('Pogledaj na stranici')
                        ->icon(Heroicon::OutlinedEye)
                        ->url(fn (Product $record) => route('gallery.show', $record), shouldOpenInNewTab: true),
                ])->tooltip('Više'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::saleAction(BulkAction::make('bulkSale'))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulkEndSale')
                        ->label('Završi akciju')
                        ->icon(Heroicon::OutlinedXMark)
                        ->action(fn (Collection $records) => $records->each->update(['discount_percent' => 0, 'discount_ends_at' => null]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulkFeature')
                        ->label('Izdvoji na naslovnoj')
                        ->icon(Heroicon::OutlinedStar)
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Još nema slika')
            ->emptyStateDescription('Dodajte prvu sliku dugmetom gore desno.');
    }

    /** "Stavi na akciju": choose a % and an optional end date, for one painting or many. */
    private static function saleAction(Action|BulkAction $action): Action|BulkAction
    {
        return $action
            ->label('Stavi na akciju')
            ->icon(Heroicon::OutlinedTag)
            ->modalHeading('Stavi na akciju')
            ->modalSubmitActionLabel('Sačuvaj')
            ->modalWidth('md')
            ->schema([
                ToggleButtons::make('discount_percent')
                    ->label('Popust')
                    ->options([10 => '10%', 15 => '15%', 20 => '20%', 25 => '25%', 30 => '30%', 40 => '40%', 50 => '50%'])
                    ->inline()
                    ->required(),
                DateTimePicker::make('discount_ends_at')
                    ->label('Traje do (nije obavezno)')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d.m.Y. H:i'),
            ])
            ->action(function (array $data, ?Product $record = null, ?Collection $records = null) {
                ($records ?? collect([$record]))->each->update([
                    'discount_percent' => (int) $data['discount_percent'],
                    'discount_ends_at' => $data['discount_ends_at'] ?? null,
                ]);
                Notification::make()->title('Akcija je postavljena.')->success()->send();
            });
    }
}
