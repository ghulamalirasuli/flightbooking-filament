<?php

namespace App\Filament\Resources\FlightBookings\Pages;

use App\Filament\Resources\FlightBookings\FlightBookingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFlightBooking extends CreateRecord
{
    protected static string $resource = FlightBookingResource::class;
}
