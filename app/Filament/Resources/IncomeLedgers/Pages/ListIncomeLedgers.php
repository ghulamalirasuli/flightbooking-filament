<?php

namespace App\Filament\Resources\IncomeLedgers\Pages;

use App\Filament\Resources\IncomeLedgers\IncomeLedgerResource;
use App\Filament\Widgets\IncomeTableWidget;
use App\Filament\Widgets\ExpenseTableWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListIncomeLedgers extends ListRecords
{
    protected static string $resource = IncomeLedgerResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            IncomeTableWidget::class,
            ExpenseTableWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    // This effectively hides the default main table
 public function table(Table $table): Table
{
    return $table
        ->emptyState(null)
        ->columns([])
        ->actions([])
        ->bulkActions([])
        ->paginated(false); // Ensures the main page bottom bar is also gone
}
}