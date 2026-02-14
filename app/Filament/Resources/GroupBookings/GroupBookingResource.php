<?php

namespace App\Filament\Resources\GroupBookings;

use App\Filament\Resources\GroupBookings\Pages\CreateGroupBooking;
use App\Filament\Resources\GroupBookings\Pages\EditGroupBooking;
use App\Filament\Resources\GroupBookings\Pages\ListGroupBookings;
use App\Filament\Resources\GroupBookings\Pages\ViewGroupBooking;
use App\Filament\Resources\GroupBookings\Schemas\GroupBookingForm;
use App\Filament\Resources\GroupBookings\Schemas\GroupBookingInfolist;
use App\Filament\Resources\GroupBookings\Tables\GroupBookingsTable;
use App\Models\GroupBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class GroupBookingResource extends Resource
{
    protected static ?string $model = GroupBooking::class;

     protected static ?int $navigationSort = 3;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'reference_no';

    public static function form(Schema $schema): Schema
    {
        return GroupBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GroupBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupBookingsTable::configure($table);
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
            'index' => ListGroupBookings::route('/'),
            'create' => CreateGroupBooking::route('/create'),
            'view' => ViewGroupBooking::route('/{record}'),
            'edit' => EditGroupBooking::route('/{record}/edit'),
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
