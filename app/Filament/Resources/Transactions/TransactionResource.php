<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\ViewTransaction;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Schemas\TransactionInfolist;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\AddTransaction as Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

use App\Filament\Resources\Transactions\RelationManagers\BatchRecordsRelationManager;


class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?int $navigationSort = 1;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static string | UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?string $recordTitleAttribute = 'reference_no';
    protected static ?string $navigationLabel = 'Transaction';

    protected static ?string $modelLabel = 'Transaction';
    
    protected static ?string $pluralModelLabel = 'Transaction';



  public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
              BatchRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'view' => ViewTransaction::route('/{record}'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }

    // App\Filament\Resources\Transactions\TransactionResource.php

public static function getRecordRouteBindingEloquentQuery(): Builder
{
    // This allows the "View" page to work even if the record is soft-deleted
    // and prepares the query for the reference_no lookup.
    return parent::getRecordRouteBindingEloquentQuery()
        ->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
}

}
