<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use App\Models\Accounts;
use App\Models\Expense_type;
use App\Models\Branch;

use Filament\Support\Icons\Heroicon;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense form')
                    ->icon(Heroicon::ArrowTurnRightDown)
                    ->iconColor('warning')
                    ->extraAttributes([
                        'style' => 'border-top: 4px solid rgb(245, 158, 11);'
                    ])
                    ->schema([

                        Grid::make(12)->schema([

                            Select::make('branch_id')
                                ->label('Branch')
                                ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('account', null);
                                    $set('currency', null);
                                    $set('type', null);
                                })
                                ->searchable()
                                ->columnSpan(6),

                            Select::make('account')
                                ->label('Account')
                                ->options(function (callable $get) {
                                    $branchId = $get('branch_id');

                                    return Accounts::query()
                                        ->with(['accountType', 'branch'])
                                        ->where('is_active', true)
                                        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                                        ->get()
                                        ->mapWithKeys(function ($account) {
                                            $name = $account->account_name;
                                            $category = $account->accountType?->accounts_category ?? 'N/A';
                                            $branch = $account->branch?->branch_name ?? 'N/A';

                                            return [
                                                $account->uid => "({$branch}) {$name} - {$category}",
                                            ];
                                        });
                                })
                                ->live()
                                ->searchable()
                                ->required()
                                ->columnSpan(6),

                        ])->columnSpanFull(),

                        Grid::make(12)->schema([

                            Select::make('expense_id')
                                ->label('Expense Type')
                                ->options(function (callable $get) {
                                    $branchId = $get('branch_id');

                                    // Return empty if no branch selected
                                    if (!$branchId) {
                                        return [];
                                    }

                                    return Expense_type::query()
                                        ->where('is_active', true)
                                        ->where('branch_id', $branchId)
                                        ->pluck('type', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->required()
                                ->columnSpan(3),

                                  Select::make('entry_type')
                                ->options([
                                    'Debit' => 'Debit',
                                    'Credit' => 'Credit',
                                ])
                                ->default('Debit')
                                ->columnSpan(3),

                            Select::make('currency')
                                ->label('Currency')
                                ->options(function (callable $get) {
                                    $branchId = $get('branch_id');

                                    // Return empty if no branch selected
                                    if (!$branchId) {
                                        return [];
                                    }

                                    // Get the branch to access active_currencies
                                    $branch = Branch::find($branchId);

                                    if (!$branch) {
                                        return [];
                                    }

                                    // Get the active currency IDs from the selected branch
                                    $activeCurrencyIds = $branch->active_currencies ?? [];

                                    if (empty($activeCurrencyIds)) {
                                        return [];
                                    }

                                    // Fetch currencies where ID is in the active_currencies array
                                    return \App\Models\Currency::query()
                                        ->whereIn('id', $activeCurrencyIds)
                                        ->where('status', true)
                                        ->pluck('currency_name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->required()
                                ->columnSpan(3),

                            TextInput::make('amount')
                                ->required()
                                ->default(0)
                                ->numeric()
                                ->columnSpan(3),


                        ])->columnSpanFull(),

                        Textarea::make('description')->columnSpanFull(),

                    ])->columnSpanFull(),
            ]);
    }
}