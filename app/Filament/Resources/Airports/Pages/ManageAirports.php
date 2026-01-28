<?php

namespace App\Filament\Resources\Airports\Pages;

use App\Filament\Resources\Airports\AirportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAirports extends ManageRecords
{
    protected static string $resource = AirportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
