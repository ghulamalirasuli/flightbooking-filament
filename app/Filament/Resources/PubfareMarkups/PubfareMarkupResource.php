<?php

namespace App\Filament\Resources\PubfareMarkups;

use App\Filament\Resources\PubfareMarkups\Pages\CreatePubfareMarkup;
use App\Filament\Resources\PubfareMarkups\Pages\EditPubfareMarkup;
use App\Filament\Resources\PubfareMarkups\Pages\ListPubfareMarkups;
use App\Filament\Resources\PubfareMarkups\Pages\ViewPubfareMarkup;
use App\Filament\Resources\PubfareMarkups\Schemas\PubfareMarkupForm;
use App\Filament\Resources\PubfareMarkups\Schemas\PubfareMarkupInfolist;
use App\Filament\Resources\PubfareMarkups\Tables\PubfareMarkupsTable;
use App\Models\PubfareMarkup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PubfareMarkupResource extends Resource
{
    protected static ?string $model = PubfareMarkup::class;

     protected static ?int $navigationSort = 5;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'supplier_id';

    public static function form(Schema $schema): Schema
    {
        return PubfareMarkupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PubfareMarkupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PubfareMarkupsTable::configure($table);
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
            'index' => ListPubfareMarkups::route('/'),
            'create' => CreatePubfareMarkup::route('/create'),
            'view' => ViewPubfareMarkup::route('/{record}'),
            'edit' => EditPubfareMarkup::route('/{record}/edit'),
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
