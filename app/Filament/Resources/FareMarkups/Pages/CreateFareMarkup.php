<?php

namespace App\Filament\Resources\FareMarkups\Pages;

use App\Filament\Resources\FareMarkups\FareMarkupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFareMarkup extends CreateRecord
{
    protected static string $resource = FareMarkupResource::class;

    protected function getRedirectUrl(): string
        {
            return $this->getResource()::getUrl('index');
        }
}
