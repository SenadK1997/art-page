<?php

namespace App\Filament\Resources\PaintingStyles\Pages;

use App\Filament\Resources\PaintingStyles\PaintingStyleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaintingStyle extends EditRecord
{
    protected static string $resource = PaintingStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
