<?php

namespace App\Filament\Resources\PubfareMarkups\Pages;

use App\Filament\Resources\PubfareMarkups\PubfareMarkupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPubfareMarkup extends ViewRecord
{
    protected static string $resource = PubfareMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
