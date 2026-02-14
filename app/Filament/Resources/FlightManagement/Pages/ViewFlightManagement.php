<?php

namespace App\Filament\Resources\FlightManagement\Pages;

use App\Filament\Resources\FlightManagement\FlightManagementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFlightManagement extends ViewRecord
{
    protected static string $resource = FlightManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
