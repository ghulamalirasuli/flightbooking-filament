<?php

namespace App\Filament\Resources\B2CfareMarkups\Pages;

use App\Filament\Resources\B2CfareMarkups\B2CfareMarkupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListB2CfareMarkups extends ListRecords
{
    protected static string $resource = B2CfareMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
