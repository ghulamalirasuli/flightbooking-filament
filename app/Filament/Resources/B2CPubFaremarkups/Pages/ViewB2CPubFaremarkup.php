<?php

namespace App\Filament\Resources\B2CPubFaremarkups\Pages;

use App\Filament\Resources\B2CPubFaremarkups\B2CPubFaremarkupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewB2CPubFaremarkup extends ViewRecord
{
    protected static string $resource = B2CPubFaremarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
