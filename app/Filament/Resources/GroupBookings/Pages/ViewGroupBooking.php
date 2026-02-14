<?php

namespace App\Filament\Resources\GroupBookings\Pages;

use App\Filament\Resources\GroupBookings\GroupBookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGroupBooking extends ViewRecord
{
    protected static string $resource = GroupBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
