<?php

namespace App\Filament\Resources\FlightManagement;

use App\Filament\Resources\FlightManagement\Pages\CreateFlightManagement;
use App\Filament\Resources\FlightManagement\Pages\EditFlightManagement;
use App\Filament\Resources\FlightManagement\Pages\ListFlightManagement;
use App\Filament\Resources\FlightManagement\Pages\ViewFlightManagement;
use App\Filament\Resources\FlightManagement\Schemas\FlightManagementForm;
use App\Filament\Resources\FlightManagement\Schemas\FlightManagementInfolist;
use App\Filament\Resources\FlightManagement\Tables\FlightManagementTable;
use App\Models\FlightManagement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FlightManagementResource extends Resource
{
    protected static ?string $model = FlightManagement::class;

     protected static ?int $navigationSort = 2;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'conn_id';

    public static function form(Schema $schema): Schema
    {
        return FlightManagementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FlightManagementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlightManagementTable::configure($table);
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
            'index' => ListFlightManagement::route('/'),
            'create' => CreateFlightManagement::route('/create'),
            'view' => ViewFlightManagement::route('/{record}'),
            'edit' => EditFlightManagement::route('/{record}/edit'),
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
