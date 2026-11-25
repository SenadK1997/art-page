<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return 'Nova slika';
    }

    public function getBreadcrumb(): string
    {
        return 'Nova';
    }

    // After saving, go back to the list (where he usually continues).
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
