<?php

namespace App\Filament\Resources\FlightManagement\Pages;

use App\Filament\Resources\FlightManagement\FlightManagementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFlightManagement extends ListRecords
{
    protected static string $resource = FlightManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
