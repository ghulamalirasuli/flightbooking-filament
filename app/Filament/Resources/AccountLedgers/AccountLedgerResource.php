<?php

namespace App\Filament\Resources\AccountLedgers;

use App\Filament\Resources\AccountLedgers\Pages\CreateAccountLedger;
use App\Filament\Resources\AccountLedgers\Pages\EditAccountLedger;
use App\Filament\Resources\AccountLedgers\Pages\ListAccountLedgers;
use App\Filament\Resources\AccountLedgers\Pages\ViewAccountLedger;
use App\Filament\Resources\AccountLedgers\Schemas\AccountLedgerForm;
use App\Filament\Resources\AccountLedgers\Schemas\AccountLedgerInfolist;
use App\Filament\Resources\AccountLedgers\Tables\AccountLedgersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Accounts; // Set this as the primary model
use App\Models\Account_ledger as AccountLedger;

use App\Filament\Resources\AccountLedgers\RelationManagers\AccountLedgersRelationManager;

class AccountLedgerResource extends Resource
{
   protected static ?string $model = Accounts::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Account Ledger';

    protected static ?string $modelLabel = 'Account Balance';
    
    protected static ?string $pluralModelLabel = 'Account Balances';

    public static function form(Schema $schema): Schema
    {
        return AccountLedgerForm::configure($schema);
    }

    // public static function infolist(Schema $schema): Schema
    // {
    //     return AccountLedgerInfolist::configure($schema);
    // }
public static function infolist(Schema $schema): Schema
    {
        // Use Schema here to match the parent Resource class
        return AccountLedgerInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            // This is the key to getting the full-width table below the header
            AccountLedgersRelationManager::class,
        ];
    }
    public static function table(Table $table): Table
    {
        return AccountLedgersTable::configure($table);
    }


    public static function getPages(): array
    {
        return [
            'index' => ListAccountLedgers::route('/'),
            // 'create' => CreateAccountLedger::route('/create'),
            'view' => ViewAccountLedger::route('/{record}'),
            // 'edit' => EditAccountLedger::route('/{record}/edit'),
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
