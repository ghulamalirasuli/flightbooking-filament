<?php

namespace App\Filament\Resources\MoneyTransfers;

use App\Filament\Resources\MoneyTransfers\Pages\CreateMoneyTransfer;
use App\Filament\Resources\MoneyTransfers\Pages\EditMoneyTransfer;
use App\Filament\Resources\MoneyTransfers\Pages\ListMoneyTransfers;
use App\Filament\Resources\MoneyTransfers\Pages\ViewMoneyTransfer;
use App\Filament\Resources\MoneyTransfers\Schemas\MoneyTransferForm;
use App\Filament\Resources\MoneyTransfers\Schemas\MoneyTransferInfolist;
use App\Filament\Resources\MoneyTransfers\Tables\MoneyTransfersTable;
use App\Models\MoneyTransfer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MoneyTransferResource extends Resource
{
    protected static ?string $model = MoneyTransfer::class;

    protected static ?int $navigationSort = 2;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-path';
    protected static string | UnitEnum | null $navigationGroup = 'Deposits';

    protected static ?string $recordTitleAttribute = 'reference_no';
    protected static ?string $navigationLabel = 'Transfer';

    protected static ?string $modelLabel = 'Transfer';
    
    protected static ?string $pluralModelLabel = 'Transfers';



    public static function form(Schema $schema): Schema
    {
        return MoneyTransferForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MoneyTransferInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MoneyTransfersTable::configure($table);
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
            'index' => ListMoneyTransfers::route('/'),
            // 'create' => CreateMoneyTransfer::route('/create'),
            // 'view' => ViewMoneyTransfer::route('/{record}'),
            // 'edit' => EditMoneyTransfer::route('/{record}/edit'),
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
