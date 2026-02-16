<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeLedgers\Pages\CreateIncomeLedger;
use App\Filament\Resources\IncomeLedgers\Pages\EditIncomeLedger;
use App\Filament\Resources\IncomeLedgers\Pages\ListIncomeLedgers;
use App\Filament\Resources\IncomeLedgers\Pages\ViewIncomeLedger;
use App\Models\Income_expense as IncomeLedger;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IncomeLedgerResource extends Resource
{
    protected static ?string $model = IncomeLedger::class;
    protected static ?string $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'reference_no';
    protected static ?string $navigationLabel = 'Income & Expense';
    protected static ?string $modelLabel = 'Income Ledger';
    protected static ?string $pluralModelLabel = 'Income & Expense Report';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // Form components if needed for create/edit
        ]);
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
            'create' => CreateIncomeLedger::route('/create'),
            'view' => ViewIncomeLedger::route('/{record}'),
            'edit' => EditIncomeLedger::route('/{record}/edit'),
        ];
    }
}