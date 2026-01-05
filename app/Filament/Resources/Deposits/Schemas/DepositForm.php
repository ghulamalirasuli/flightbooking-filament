<?php

namespace App\Filament\Resources\Deposits\Schemas;

use Filament\Schemas\Schema;
use App\Models\Branch;
use App\Models\Accounts;
use App\Models\Currency;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea; 

class DepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Grid::make(12)
                    ->schema([

                           Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('account', null);
                                $set('currency', null);
                            })
                            ->searchable()
                            ->columnSpan(6),
                                         /* 1. Account (Now Live) */
                Select::make('account')
                ->label('Account')
                ->options(function (callable $get) {
                    $branchId = $get('branch_id');

                    return \App\Models\Accounts::query()
                        ->with(['accountType', 'branch']) // Eager load for performance
                        ->where('is_active', true)
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->get()
                        ->mapWithKeys(function ($account) {
                            // Using your specific formatting logic
                            $name = $account->account_name;
                            $category = $account->accountType?->accounts_category ?? 'N/A';
                            $branch = $account->branch?->branch_name ?? 'N/A';

                            return [
                                $account->uid => "({$branch}) {$name} - {$category}"
                            ];
                        });
                })
                ->live()
                ->afterStateUpdated(fn ($set) => $set('currency', null))
                ->searchable()
            ->columnSpan(6),
             
                ])->columnSpanFull(),
                /* Row 2:  Account(4) | Currency(4) | Service(4) */
              Grid::make(12)
                    ->schema([
       
             Select::make('entry_type')
                            ->label('Deposit Type')
                            ->options([
                                'Debit' => 'Debit',
                                'Credit' => 'Credit',
                            ])->required()->columnSpan(4),

                Select::make('currency_id')
                            ->label('Currency')
                            ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                            ->searchable()
                            ->columnSpan(4),
                        TextInput::make('amount_from')
                            ->label("Amount")
                            ->required()
                            ->numeric()
                            ->columnSpan(4),
    ])
    ->columnSpanFull(),
      
                /* Row 4: Description (Full Width) */
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
 
            ]);
    }
}
