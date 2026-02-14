<?php

namespace App\Filament\Resources\FareMarkups\Pages;

use App\Filament\Resources\FareMarkups\FareMarkupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFareMarkup extends ViewRecord
{
    protected static string $resource = FareMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
