<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Models\ProductSize;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Akcija')
                    ->schema([
                        TextInput::make('title')
                            ->label('Naziv')
                            ->placeholder('Kupi dvije, treća gratis')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('description')
                            ->label('Kratki opis (nije obavezno)')
                            ->helperText('Ako ostavite prazno, uslovi se napišu sami.')
                            ->maxLength(200),
                        Toggle::make('is_active')->label('Aktivna')->default(true),
                        DateTimePicker::make('starts_at')->label('Počinje')->native(false)->seconds(false)->displayFormat('d.m.Y. H:i'),
                        DateTimePicker::make('ends_at')->label('Završava')->native(false)->seconds(false)->displayFormat('d.m.Y. H:i')->after('starts_at'),
                    ]),

                Section::make('Uslovi')
                    ->description('Kupac dobija poklon kada su ispunjeni SVI uslovi koje popunite. Slike na akciji se računaju normalno.')
                    ->schema([
                        Select::make('requiredProducts')
                            ->label('Mora kupiti ove slike')
                            ->helperText('Npr. „Sebilj“ i „Baščaršija“. Prazno = bilo koje slike.')
                            ->relationship('requiredProducts', 'title')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        TextInput::make('min_paintings')
                            ->label('Najmanje slika u korpi')
                            ->numeric()->integer()->minValue(1)
                            ->placeholder('npr. 2'),
                        TextInput::make('min_total')
                            ->label('Najmanji iznos narudžbe')
                            ->numeric()->integer()->minValue(1)
                            ->suffix('KM')
                            ->placeholder('npr. 500'),
                    ]),

                Section::make('Slike koje se mogu izabrati na poklon')
                    ->description('Kupac bira jednu. Dobro je ponuditi manje formate. Ako poklon ima stanje, uzima se sa stanja.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('gifts')
                            ->hiddenLabel()
                            ->relationship('gifts', 'id', fn ($query) => $query->with('product')->orderBy('price'))
                            ->getOptionLabelFromRecordUsing(fn (ProductSize $record) => $record->product->title.' — '.$record->label().' (inače '.$record->price.' KM)')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),
                    ]),
            ]);
    }
}
