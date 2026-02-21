<?php

namespace App\Filament\Resources\IncomeLedgers;

use App\Filament\Resources\IncomeLedgers\Pages\CreateIncomeLedger;
use App\Filament\Resources\IncomeLedgers\Pages\EditIncomeLedger;
use App\Filament\Resources\IncomeLedgers\Pages\ListIncomeLedgers;
use App\Filament\Resources\IncomeLedgers\Pages\ViewIncomeLedger;
use App\Filament\Resources\IncomeLedgers\Schemas\IncomeLedgerForm;
use App\Filament\Resources\IncomeLedgers\Schemas\IncomeLedgerInfolist;
use App\Filament\Resources\IncomeLedgers\Tables\IncomeLedgersTable;
use App\Models\Income_expense as IncomeLedger;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
// --- ADD THESE TWO IMPORTS ---
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IncomeLedgerResource extends Resource
{
    protected static ?string $model = IncomeLedger::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'reference_no';

    /**
     * This method applies the branch-scoping logic from your original BIncomeController.
     * It ensures users only see data they are authorized to see.
     */
    // public static function getEloquentQuery(): Builder
    // {
    //     $user = Auth::user();
    //     $query = parent::getEloquentQuery();

    //     // If the user is NOT a Superuser or Admin, filter by their branch_id
    //     if ($user && !in_array($user->user_type, ['Superuser', 'Admin'])) {
    //         $query->where('branch_id', $user->branch_id);
    //     }

    //     return $query;
    // }

    public static function form(Schema $schema): Schema
    {
        return IncomeLedgerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IncomeLedgerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomeLedgersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomeLedgers::route('/'),
            // 'create' => CreateIncomeLedger::route('/create'),
            'view' => ViewIncomeLedger::route('/{record}'),
            // 'edit' => EditIncomeLedger::route('/{record}/edit'),
        ];
    }
}