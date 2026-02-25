<?php

namespace App\Filament\Resources\ExpenseTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

use App\Models\Expense_type;
use App\Models\Service;
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
                            ->searchable()
                            ->columnSpan(3),

                  Select::make('service_id')
                            ->label('Expense Category')
                            ->options(Service::where('status', true)->where('is_income', false)->pluck('title', 'id'))
                            ->searchable()
                            ->columnSpan(3),

                TextInput::make('name')
                    ->label('Expense Name')
                    ->required()
                    ->maxLength(255)->columnSpan(6)
                     ->helperText('Expense name should be unique within the same branch and category'),
                ])->columnSpanFull(),
            ]);
    }
}
