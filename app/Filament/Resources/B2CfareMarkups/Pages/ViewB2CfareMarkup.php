<?php

namespace App\Filament\Resources\B2CfareMarkups\Pages;

use App\Filament\Resources\B2CfareMarkups\B2CfareMarkupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewB2CfareMarkup extends ViewRecord
{
    protected static string $resource = B2CfareMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
