<?php

namespace App\Filament\Widgets;

use App\Models\Income_expense;
use App\Filament\Resources\IncomeLedgers\IncomeLedgerResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class IncomeTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Income';
    protected int | string | array $columnSpan = 1;

 public function table(Table $table): Table
{
    return $table
        // Eager load 'service' to prevent 'not found' issues
        ->query(Income_expense::query()->with(['service'])->where('credit', '>', 0))
        ->paginated(false) 
        ->columns([
            Tables\Columns\TextColumn::make('created_at')
                ->label('Date')
                ->date(),

            // Make sure this matches the relationship name 'service' 
            // and the field 'title' in the our_service table
            Tables\Columns\TextColumn::make('service.title')
                ->label('Service Name')
                ->placeholder('No Service')
                ->searchable(),
            
            Tables\Columns\TextColumn::make('credit')
                ->label('Income Amount')
                ->numeric()
                ->summarize(Tables\Columns\Summarizers\Sum::make())
                ->url(fn ($record) => IncomeLedgerResource::getUrl('view', ['record' => $record]))
                ->color('success')
                ->weight('bold'),
        ]);
}
}