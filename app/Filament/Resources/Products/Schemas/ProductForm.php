<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\PaintingStyle;
use App\Models\Tag;
use App\Support\PriceCalculator;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * One simple top-to-bottom flow, also comfortable on a phone:
 * photo → name and style → sizes and prices → sale → more details → visibility.
 */
class ProductForm
{
    /** Sizes offered by "Dodaj standardne veličine" (kept inside the style's limits). */
    private const STANDARD_SIZES = [[30, 40], [50, 70], [70, 100]];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('1 · Fotografija')
                    ->description('Glavna fotografija slike. Dodatne fotografije (detalji, slika na zidu) su dolje u „Više detalja“.')
                    ->schema([
                        FileUpload::make('image')
                            ->hiddenLabel()
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('paintings')
                            ->visibility('public')
                            ->maxSize(15 * 1024)
                            ->automaticallyResizeImagesMode('contain')
                            ->automaticallyResizeImagesToWidth('2400')
                            ->automaticallyResizeImagesToHeight('2400')
                            ->imagePreviewHeight('280')
                            ->required()
                            ->validationMessages(['required' => 'Dodajte fotografiju slike.']),
                    ]),

                Section::make('2 · Naziv i stil')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('title')
                            ->label('Naziv')
                            ->placeholder('Sebilj u sumrak')
                            ->required()
                            ->maxLength(150),
                        Select::make('painting_style_id')
                            ->label('Stil')
                            ->relationship('style', 'name')
                            ->preload()
                            ->native(false)
                            ->live()
                            ->helperText('Stil određuje cijenu po formuli.'),
                        Select::make('tags')
                            ->label('Kategorije')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->placeholder('Npr. Sarajevo, Priroda')
                            ->createOptionForm([
                                TextInput::make('name')->label('Naziv kategorije')->required(),
                            ])
                            ->createOptionUsing(fn (array $data) => Tag::firstOrCreate(
                                ['slug' => Str::slug($data['name'])],
                                ['name' => $data['name']],
                            )->getKey())
                            ->columnSpanFull(),
                    ]),

                Section::make('3 · Veličine i cijene')
                    ->key('sizes-section')
                    ->description(fn (Get $get) => self::formulaHint($get('painting_style_id')))
                    ->headerActions([
                        Action::make('standardSizes')
                            ->label('Dodaj standardne veličine')
                            ->icon(Heroicon::OutlinedSquaresPlus)
                            ->color('gray')
                            ->action(fn (Get $get, Set $set) => self::addStandardSizes($get, $set)),
                        Action::make('recalculate')
                            ->label('Izračunaj sve cijene')
                            ->icon(Heroicon::OutlinedCalculator)
                            ->color('gray')
                            ->requiresConfirmation()
                            ->modalDescription('Sve cijene ispod će se popuniti po formuli za izabrani stil. Kasnije ih možete ručno promijeniti.')
                            ->action(function (Get $get, Set $set) {
                                $style = PaintingStyle::find($get('painting_style_id'));
                                if (! $style) {
                                    Notification::make()->title('Prvo izaberite stil slike.')->warning()->send();

                                    return;
                                }
                                $set('sizes', collect($get('sizes') ?? [])->map(function ($size) use ($style) {
                                    if (filled($size['width'] ?? null) && filled($size['height'] ?? null)) {
                                        $size['price'] = $style->priceFor((int) $size['width'], (int) $size['height']);
                                    }

                                    return $size;
                                })->all());
                                Notification::make()->title('Cijene su izračunate.')->success()->send();
                            }),
                    ])
                    ->schema([
                        // Cards that sit two-by-two on a phone, in one row on a computer.
                        Repeater::make('sizes')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('sort')
                            ->columns(['default' => 2, 'md' => 4])
                            ->itemLabel(fn (array $state) => filled($state['width'] ?? null) && filled($state['height'] ?? null)
                                ? $state['width'].' × '.$state['height'].' cm'.(filled($state['price'] ?? null) ? ' — '.$state['price'].' KM' : '')
                                : 'Nova veličina')
                            ->schema([
                                TextInput::make('width')
                                    ->label('Širina (cm)')
                                    ->numeric()->integer()->minValue(5)->maxValue(500)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::fillPrice($get, $set)),
                                TextInput::make('height')
                                    ->label('Visina (cm)')
                                    ->numeric()->integer()->minValue(5)->maxValue(500)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::fillPrice($get, $set)),
                                TextInput::make('price')
                                    ->label('Cijena')
                                    ->numeric()->integer()->minValue(1)
                                    ->required()
                                    ->suffix('KM'),
                                TextInput::make('stock')
                                    ->label('Na stanju (kom)')
                                    ->numeric()->integer()->minValue(0)->maxValue(999)
                                    ->default(0)
                                    ->required(),
                            ])
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('+ Dodaj veličinu')
                            ->validationMessages(['min' => 'Dodajte bar jednu veličinu.']),
                    ]),

                Section::make('4 · Akcija (popust)')
                    ->description('Slike na akciji se prikazuju na naslovnoj sa precrtanom starom cijenom.')
                    ->collapsible()
                    ->collapsed(fn (Get $get) => ! (int) $get('discount_percent'))
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        ToggleButtons::make('discount_percent')
                            ->label('Popust')
                            ->options([0 => 'Bez', 10 => '10%', 15 => '15%', 20 => '20%', 25 => '25%', 30 => '30%', 40 => '40%', 50 => '50%'])
                            ->inline()
                            ->default(0)
                            ->live()
                            ->columnSpanFull(),
                        DateTimePicker::make('discount_ends_at')
                            ->label('Akcija traje do')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d.m.Y. H:i')
                            ->helperText('Prazno = dok je ručno ne ugasite.')
                            ->visible(fn (Get $get) => (int) $get('discount_percent') > 0),
                    ]),

                Section::make('Više detalja')
                    ->description('Nije obavezno: tehnika, godina, opis i dodatne fotografije.')
                    ->collapsible()
                    ->collapsed()
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('technique')
                            ->label('Tehnika')
                            ->placeholder('Ulje na platnu')
                            ->maxLength(100),
                        TextInput::make('year')
                            ->label('Godina')
                            ->numeric()->integer()
                            ->minValue(1950)->maxValue((int) date('Y') + 1),
                        Textarea::make('description')
                            ->label('Opis')
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        FileUpload::make('gallery')
                            ->label('Dodatne fotografije')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->panelLayout('grid')
                            ->disk('public')
                            ->directory('paintings')
                            ->visibility('public')
                            ->maxSize(15 * 1024)
                            ->maxFiles(8)
                            ->automaticallyResizeImagesMode('contain')
                            ->automaticallyResizeImagesToWidth('2400')
                            ->automaticallyResizeImagesToHeight('2400')
                            ->columnSpanFull(),
                    ]),

                Section::make('Prikaz')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        Toggle::make('is_available')
                            ->label('Prikaži u galeriji')
                            ->helperText('Isključite da sakrijete sliku.')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Izdvoji na naslovnoj'),
                        Toggle::make('made_to_order')
                            ->label('Može se naslikati ponovo')
                            ->helperText('Isključite za unikat: kad se proda, piše „Prodano“.')
                            ->default(true),
                    ]),
            ]);
    }

    private static function fillPrice(Get $get, Set $set): void
    {
        $width = (int) $get('width');
        $height = (int) $get('height');
        $style = PaintingStyle::find($get('../../painting_style_id'));

        if ($style && $width > 0 && $height > 0) {
            $set('price', PriceCalculator::price($width, $height, $style->multiplier));
        }
    }

    /** Add the common sizes (with formula prices) that aren't in the list yet. */
    private static function addStandardSizes(Get $get, Set $set): void
    {
        $style = PaintingStyle::find($get('painting_style_id'));
        if (! $style) {
            Notification::make()->title('Prvo izaberite stil slike.')->warning()->send();

            return;
        }

        $sizes = collect($get('sizes') ?? [])
            // Drop the empty row a new painting starts with.
            ->filter(fn ($s) => filled($s['width'] ?? null) || filled($s['height'] ?? null));
        $existing = $sizes->map(fn ($s) => ($s['width'] ?? '').'x'.($s['height'] ?? ''))->all();

        $added = 0;
        foreach (self::STANDARD_SIZES as [$w, $h]) {
            if (in_array("{$w}x{$h}", $existing, true) || ! $style->acceptsSize($w, $h)) {
                continue;
            }
            $sizes->put((string) Str::uuid(), [
                'width' => $w,
                'height' => $h,
                'price' => $style->priceFor($w, $h),
                'stock' => 0,
            ]);
            $added++;
        }

        $set('sizes', $sizes->all());
        Notification::make()
            ->title($added ? "Dodano veličina: {$added}" : 'Standardne veličine su već dodane.')
            ->success()
            ->send();
    }

    private static function formulaHint(?int $styleId): string
    {
        $style = $styleId ? PaintingStyle::find($styleId) : null;

        if (! $style) {
            return 'Izaberite stil (korak 2) i cijena se sama popuni kada upišete širinu i visinu. Cijenu uvijek možete ručno promijeniti.';
        }

        return "Cijena se sama popuni po formuli: širina × visina ÷ 100 × {$style->multiplier} KM ({$style->name}). Npr. 50 × 70 cm = "
            .$style->priceFor(50, 70).' KM. Uvijek je možete ručno promijeniti.';
    }
}
