<?php

namespace App\Filament\Resources\B2CPubFaremarkups;

use App\Filament\Resources\B2CPubFaremarkups\Pages\CreateB2CPubFaremarkup;
use App\Filament\Resources\B2CPubFaremarkups\Pages\EditB2CPubFaremarkup;
use App\Filament\Resources\B2CPubFaremarkups\Pages\ListB2CPubFaremarkups;
use App\Filament\Resources\B2CPubFaremarkups\Pages\ViewB2CPubFaremarkup;
use App\Filament\Resources\B2CPubFaremarkups\Schemas\B2CPubFaremarkupForm;
use App\Filament\Resources\B2CPubFaremarkups\Schemas\B2CPubFaremarkupInfolist;
use App\Filament\Resources\B2CPubFaremarkups\Tables\B2CPubFaremarkupsTable;
use App\Models\B2CPubFare as B2CPubFaremarkup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class B2CPubFaremarkupResource extends Resource
{
    protected static ?string $model = B2CPubFaremarkup::class;

     protected static ?int $navigationSort = 7;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'supplier_id';

    public static function form(Schema $schema): Schema
    {
        return B2CPubFaremarkupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return B2CPubFaremarkupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return B2CPubFaremarkupsTable::configure($table);
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
            'index' => ListB2CPubFaremarkups::route('/'),
            'create' => CreateB2CPubFaremarkup::route('/create'),
            'view' => ViewB2CPubFaremarkup::route('/{record}'),
            'edit' => EditB2CPubFaremarkup::route('/{record}/edit'),
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
