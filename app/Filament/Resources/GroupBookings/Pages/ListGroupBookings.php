<?php

namespace App\Filament\Resources\GroupBookings\Pages;

use App\Filament\Resources\GroupBookings\GroupBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGroupBookings extends ListRecords
{
    protected static string $resource = GroupBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
