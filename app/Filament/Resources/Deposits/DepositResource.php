<?php

namespace App\Filament\Resources\Deposits;

use App\Filament\Resources\Deposits\Pages\CreateDeposit;
use App\Filament\Resources\Deposits\Pages\EditDeposit;
use App\Filament\Resources\Deposits\Pages\ListDeposits;
use App\Filament\Resources\Deposits\Pages\ViewDeposit;
use App\Filament\Resources\Deposits\Schemas\DepositForm;
use App\Filament\Resources\Deposits\Schemas\DepositInfolist;
use App\Filament\Resources\Deposits\Tables\DepositsTable;
use App\Models\CashBox as Deposit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Deposit';

      protected static ?string $navigationLabel = 'Deposit';

    protected static ?string $modelLabel = 'Deposit';
    
    protected static ?string $pluralModelLabel = 'Deposits';


    public static function form(Schema $schema): Schema
    {
        return DepositForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DepositInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepositsTable::configure($table);
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
            'index' => ListDeposits::route('/'),
            // 'create' => CreateDeposit::route('/create'),
            // 'view' => ViewDeposit::route('/{record}'),
            // 'edit' => EditDeposit::route('/{record}/edit'),
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
