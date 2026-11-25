<?php

namespace App\Filament\Resources\PaintingStyles\Schemas;

use App\Support\PriceCalculator;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PaintingStyleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Stil')
                    ->schema([
                        TextInput::make('name')
                            ->label('Naziv')
                            ->required()
                            ->maxLength(60)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Adresa (URL)')
                            ->helperText('Koristi se u linku galerije, npr. /galerija?stil=realizam')
                            ->required()
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        TextInput::make('multiplier')
                            ->label('Cijena za 100 cm² (KM)')
                            ->helperText(fn (Get $get) => 'Formula: širina × visina ÷ 100 × ovaj broj. Npr. 50 × 70 cm = '
                                .PriceCalculator::price(50, 70, (int) $get('multiplier')).' KM, 100 × 150 cm = '
                                .PriceCalculator::price(100, 150, (int) $get('multiplier')).' KM.')
                            ->numeric()->integer()->minValue(1)->maxValue(1000)
                            ->required()
                            ->live(onBlur: true)
                            ->suffix('KM'),
                        TextInput::make('description')
                            ->label('Kratki opis')
                            ->maxLength(160),
                        Toggle::make('is_active')
                            ->label('Prikaži u kalkulatoru')
                            ->default(true),
                    ]),
                Section::make('Slika stila')
                    ->description('Slika na naslovnoj (Apstrakcija / Realizam / Panorama) i u kalkulatoru. Ako je prazno, koristi se jedna od slika ovog stila iz galerije.')
                    ->schema([
                        FileUpload::make('image')
                            ->hiddenLabel()
                            ->imagePreviewHeight('240')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('styles')
                            ->visibility('public')
                            ->maxSize(15 * 1024)
                            ->automaticallyResizeImagesMode('contain')
                            ->automaticallyResizeImagesToWidth('1600')
                            ->automaticallyResizeImagesToHeight('1600'),
                    ]),
                Section::make('Dozvoljene veličine po mjeri')
                    ->description('Kupac u kalkulatoru može birati samo veličine u ovim granicama.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('min_width')->label('Najmanja širina')->numeric()->integer()->minValue(5)->default(20)->suffix('cm')->required(),
                        TextInput::make('max_width')->label('Najveća širina')->numeric()->integer()->gte('min_width')->default(200)->suffix('cm')->required(),
                        TextInput::make('min_height')->label('Najmanja visina')->numeric()->integer()->minValue(5)->default(20)->suffix('cm')->required(),
                        TextInput::make('max_height')->label('Najveća visina')->numeric()->integer()->gte('min_height')->default(200)->suffix('cm')->required(),
                        Toggle::make('allows_photo')
                            ->label('Kupac može poslati svoju fotografiju')
                            ->helperText('Npr. kuća, grad ili omiljeno mjesto po fotografiji kupca.')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
