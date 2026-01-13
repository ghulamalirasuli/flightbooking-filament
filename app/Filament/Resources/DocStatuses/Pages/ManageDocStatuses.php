<?php

namespace App\Filament\Resources\DocStatuses\Pages;

use App\Filament\Resources\DocStatuses\DocStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDocStatuses extends ManageRecords
{
    protected static string $resource = DocStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
