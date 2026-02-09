<?php

namespace App\Filament\Resources\Transactions;

use Filament\Resources\Resource;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\ViewTransaction;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Schemas\TransactionInfolist;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\AddTransaction as Transaction;
use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // ✅ Add this import

use UnitEnum;
use BackedEnum;

use Illuminate\Contracts\Support\Htmlable; // ✅ Add this for return type

use App\Filament\Resources\Transactions\RelationManagers\BatchRecordsRelationManager;
use App\Filament\Resources\Transactions\RelationManagers\TransactionAccountRelationManager;
use App\Filament\Resources\Transactions\RelationManagers\TransactionCommentsRelationManager;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?int $navigationSort = 1;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static string | UnitEnum | null $navigationGroup = 'Transactions';

    //1->
    protected static ?string $recordTitleAttribute = 'reference_no';
    protected static ?string $navigationLabel = 'Transaction';

    protected static ?string $modelLabel = 'Transaction';
    
    protected static ?string $pluralModelLabel = 'Transaction';

//2--->
// Override this method to use multiple fields
public static function getGloballySearchableAttributes(): array
{
    return ['reference_no', 'fullname'];
}

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
              TransactionAccountRelationManager::class,
              TransactionCommentsRelationManager::class,
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
