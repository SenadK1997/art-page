<?php

namespace App\Filament\Resources\PaintingStyles\Pages;

use App\Filament\Resources\PaintingStyles\PaintingStyleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaintingStyles extends ListRecords
{
    protected static string $resource = PaintingStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
