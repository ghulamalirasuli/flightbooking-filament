<?php

namespace App\Filament\Resources\DocTypes\Pages;

use App\Filament\Resources\DocTypes\DocTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDocTypes extends ManageRecords
{
    protected static string $resource = DocTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
