<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Filament\Resources\IncomeLedgers\IncomeLedgerResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpenseTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Expense';
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(Expense::query()->where('debit', '>', 0))
            ->paginated(false) // This removes the bottom pagination bar
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date(),

                // Added Expense Type Column (from your Expense model relationship)
                Tables\Columns\TextColumn::make('expenseType.type')
                    ->label('Expense Type')
                    ->placeholder('General'),

                Tables\Columns\TextColumn::make('debit')
                    ->label('Expense Amount')
                    ->numeric()
                    ->summarize(Tables\Columns\Summarizers\Sum::make())
                    ->url(fn ($record) => IncomeLedgerResource::getUrl('view', ['record' => $record]))
                    ->color('danger')
                    ->weight('bold'),
            ]);
    }
}