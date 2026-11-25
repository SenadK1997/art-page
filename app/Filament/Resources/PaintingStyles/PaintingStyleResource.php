<?php

namespace App\Filament\Resources\PaintingStyles;

use App\Filament\Resources\PaintingStyles\Pages\CreatePaintingStyle;
use App\Filament\Resources\PaintingStyles\Pages\EditPaintingStyle;
use App\Filament\Resources\PaintingStyles\Pages\ListPaintingStyles;
use App\Filament\Resources\PaintingStyles\Schemas\PaintingStyleForm;
use App\Filament\Resources\PaintingStyles\Tables\PaintingStylesTable;
use App\Models\PaintingStyle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaintingStyleResource extends Resource
{
    protected static ?string $model = PaintingStyle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $modelLabel = 'stil';

    protected static ?string $pluralModelLabel = 'Stilovi i cijene';

    protected static ?string $navigationLabel = 'Stilovi i cijene';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PaintingStyleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaintingStylesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaintingStyles::route('/'),
            'create' => CreatePaintingStyle::route('/create'),
            'edit' => EditPaintingStyle::route('/{record}/edit'),
        ];
    }
}
