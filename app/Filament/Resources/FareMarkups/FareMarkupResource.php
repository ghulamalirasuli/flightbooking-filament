<?php

namespace App\Filament\Resources\FareMarkups;

use App\Filament\Resources\FareMarkups\Pages\CreateFareMarkup;
use App\Filament\Resources\FareMarkups\Pages\EditFareMarkup;
use App\Filament\Resources\FareMarkups\Pages\ListFareMarkups;
use App\Filament\Resources\FareMarkups\Pages\ViewFareMarkup;
use App\Filament\Resources\FareMarkups\Schemas\FareMarkupForm;
use App\Filament\Resources\FareMarkups\Schemas\FareMarkupInfolist;
use App\Filament\Resources\FareMarkups\Tables\FareMarkupsTable;
use App\Models\FareMarkup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FareMarkupResource extends Resource
{
    protected static ?string $model = FareMarkup::class;

    protected static ?int $navigationSort = 4;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'airlines';

    public static function form(Schema $schema): Schema
    {
        return FareMarkupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FareMarkupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FareMarkupsTable::configure($table);
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
            'index' => ListFareMarkups::route('/'),
            'create' => CreateFareMarkup::route('/create'),
            'view' => ViewFareMarkup::route('/{record}'),
            'edit' => EditFareMarkup::route('/{record}/edit'),
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
