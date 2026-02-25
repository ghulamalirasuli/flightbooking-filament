<?php

namespace App\Filament\Resources\ExpenseTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

use App\Models\Branch;

class ExpenseTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)->schema([

                  Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                  $set('from_account', null);
                                  $set('currency_id', null);
                              })
                            ->searchable()
                            ->columnSpan(4),

                TextInput::make('type')
                    ->label('Expense Type')
                    ->required()
                    ->maxLength(255)->columnSpan(8),
                ])->columnSpanFull(),
            ]);
    }
}
