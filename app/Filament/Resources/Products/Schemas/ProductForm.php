<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\PaintingStyle;
use App\Support\PriceCalculator;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Slika')
                            ->description('Glavna fotografija se prikazuje u galeriji. Dodatne fotografije (detalji, slika na zidu) su na stranici slike.')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Glavna fotografija')
                                    ->image()
                                    ->imageEditor()
                                    ->disk('public')
                                    ->directory('paintings')
                                    ->visibility('public')
                                    ->maxSize(15 * 1024)
                                    ->automaticallyResizeImagesMode('contain')
                                    ->automaticallyResizeImagesToWidth('2400')
                                    ->automaticallyResizeImagesToHeight('2400')
                                    ->required(),
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
                                    ->automaticallyResizeImagesToHeight('2400'),
                            ]),

                        Section::make('Veličine i cijene')
                            ->key('sizes-section')
                            ->description(fn (Get $get) => self::formulaHint($get('painting_style_id')))
                            ->headerActions([
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
                                        $sizes = collect($get('sizes') ?? [])->map(function ($size) use ($style) {
                                            if (filled($size['width'] ?? null) && filled($size['height'] ?? null)) {
                                                $size['price'] = $style->priceFor((int) $size['width'], (int) $size['height']);
                                            }

                                            return $size;
                                        })->all();
                                        $set('sizes', $sizes);
                                        Notification::make()->title('Cijene su izračunate.')->success()->send();
                                    }),
                            ])
                            ->schema([
                                Repeater::make('sizes')
                                    ->hiddenLabel()
                                    ->relationship()
                                    ->orderColumn('sort')
                                    ->table([
                                        TableColumn::make('Širina (cm)'),
                                        TableColumn::make('Visina (cm)'),
                                        TableColumn::make('Cijena (KM)'),
                                        TableColumn::make('Na stanju (kom)'),
                                    ])
                                    ->schema([
                                        TextInput::make('width')
                                            ->numeric()->integer()->minValue(5)->maxValue(500)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::fillPrice($get, $set)),
                                        TextInput::make('height')
                                            ->numeric()->integer()->minValue(5)->maxValue(500)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::fillPrice($get, $set)),
                                        TextInput::make('price')
                                            ->numeric()->integer()->minValue(1)
                                            ->required()
                                            ->suffix('KM'),
                                        TextInput::make('stock')
                                            ->numeric()->integer()->minValue(0)->maxValue(999)
                                            ->default(0)
                                            ->required(),
                                    ])
                                    ->defaultItems(1)
                                    ->minItems(1)
                                    ->addActionLabel('Dodaj veličinu')
                                    ->validationMessages(['min' => 'Dodajte bar jednu veličinu.']),
                            ]),
                    ]),

                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Podaci')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Naziv')
                                    ->required()
                                    ->maxLength(150),
                                Select::make('painting_style_id')
                                    ->label('Stil')
                                    ->relationship('style', 'name')
                                    ->preload()
                                    ->live()
                                    ->helperText('Stil određuje cijenu po formuli.'),
                                TextInput::make('technique')
                                    ->label('Tehnika')
                                    ->placeholder('Ulje na platnu')
                                    ->maxLength(100),
                                TextInput::make('year')
                                    ->label('Godina')
                                    ->numeric()->integer()
                                    ->minValue(1950)->maxValue((int) date('Y') + 1),
                                Select::make('tags')
                                    ->label('Kategorije')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->label('Naziv kategorije')->required(),
                                    ])
                                    ->createOptionUsing(fn (array $data) => \App\Models\Tag::firstOrCreate(
                                        ['slug' => Str::slug($data['name'])],
                                        ['name' => $data['name']],
                                    )->getKey()),
                                Textarea::make('description')
                                    ->label('Opis')
                                    ->rows(6)
                                    ->maxLength(5000),
                            ]),

                        Section::make('Akcija')
                            ->description('Slike na akciji se prikazuju na naslovnoj sa precrtanom starom cijenom.')
                            ->schema([
                                TextInput::make('discount_percent')
                                    ->label('Popust')
                                    ->numeric()->integer()->minValue(0)->maxValue(90)
                                    ->default(0)
                                    ->suffix('%')
                                    ->helperText('0 = nema akcije.'),
                                DateTimePicker::make('discount_ends_at')
                                    ->label('Akcija traje do')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat('d.m.Y. H:i')
                                    ->helperText('Prazno = dok je ručno ne ugasite.'),
                            ]),

                        Section::make('Prikaz')
                            ->schema([
                                Toggle::make('is_available')
                                    ->label('Prikaži u galeriji')
                                    ->helperText('Isključite kada je slika prodana.')
                                    ->default(true),
                                Toggle::make('made_to_order')
                                    ->label('Može se naslikati po narudžbi')
                                    ->helperText('Isključite za unikatni original: kada se prodaju komadi na stanju, slika je označena kao prodana.')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Izdvoji na naslovnoj')
                                    ->helperText('Prva izdvojena slika je velika slika na vrhu naslovne.'),
                            ]),
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

    private static function formulaHint(?int $styleId): string
    {
        $style = $styleId ? PaintingStyle::find($styleId) : null;

        if (! $style) {
            return 'Izaberite stil (desno) i cijena će se sama popuniti kada upišete širinu i visinu. Cijenu uvijek možete ručno promijeniti.';
        }

        return "Cijena se sama popunjava po formuli: širina × visina ÷ 100 × {$style->multiplier} KM ({$style->name}). Primjer: 50 × 70 cm = "
            .$style->priceFor(50, 70).' KM. Cijenu uvijek možete ručno promijeniti.';
    }
}
