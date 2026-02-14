<?php

namespace App\Filament\Resources\FlightBookings;

use App\Filament\Resources\FlightBookings\Pages\CreateFlightBooking;
use App\Filament\Resources\FlightBookings\Pages\EditFlightBooking;
use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Filament\Resources\FlightBookings\Pages\ViewFlightBooking;
use App\Filament\Resources\FlightBookings\Schemas\FlightBookingForm;
use App\Filament\Resources\FlightBookings\Schemas\FlightBookingInfolist;
use App\Filament\Resources\FlightBookings\Tables\FlightBookingsTable;
use App\Models\FlightBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FlightBookingResource extends Resource
{
    protected static ?string $model = FlightBooking::class;

     protected static ?int $navigationSort = 1;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'conn_id';

    public static function form(Schema $schema): Schema
    {
        return FlightBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlightBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlightBookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFlightBookings::route('/'),
            'create' => CreateFlightBooking::route('/create'),
            'view' => ViewFlightBooking::route('/{record}'),
            'edit' => EditFlightBooking::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
