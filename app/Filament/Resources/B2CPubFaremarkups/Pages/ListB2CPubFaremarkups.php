<?php

namespace App\Filament\Resources\B2CPubFaremarkups\Pages;

use App\Filament\Resources\B2CPubFaremarkups\B2CPubFaremarkupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListB2CPubFaremarkups extends ListRecords
{
    protected static string $resource = B2CPubFaremarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
