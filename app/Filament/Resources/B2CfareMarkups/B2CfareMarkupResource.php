<?php

namespace App\Filament\Resources\B2CfareMarkups;

use App\Filament\Resources\B2CfareMarkups\Pages\CreateB2CfareMarkup;
use App\Filament\Resources\B2CfareMarkups\Pages\EditB2CfareMarkup;
use App\Filament\Resources\B2CfareMarkups\Pages\ListB2CfareMarkups;
use App\Filament\Resources\B2CfareMarkups\Pages\ViewB2CfareMarkup;
use App\Filament\Resources\B2CfareMarkups\Schemas\B2CfareMarkupForm;
use App\Filament\Resources\B2CfareMarkups\Schemas\B2CfareMarkupInfolist;
use App\Filament\Resources\B2CfareMarkups\Tables\B2CfareMarkupsTable;
use App\Models\B2CFare as B2CfareMarkup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class B2CfareMarkupResource extends Resource
{
    protected static ?string $model = B2CfareMarkup::class;

   protected static ?int $navigationSort = 6;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'airlines';

    public static function form(Schema $schema): Schema
    {
        return B2CfareMarkupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return B2CfareMarkupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return B2CfareMarkupsTable::configure($table);
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
            'index' => ListB2CfareMarkups::route('/'),
            'create' => CreateB2CfareMarkup::route('/create'),
            'view' => ViewB2CfareMarkup::route('/{record}'),
            'edit' => EditB2CfareMarkup::route('/{record}/edit'),
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
