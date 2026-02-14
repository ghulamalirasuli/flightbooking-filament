<?php

namespace App\Filament\Resources\FareMarkups\Pages;

use App\Filament\Resources\FareMarkups\FareMarkupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFareMarkups extends ListRecords
{
    protected static string $resource = FareMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
