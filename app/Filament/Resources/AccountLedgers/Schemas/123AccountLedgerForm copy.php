<?php

namespace App\Filament\Resources\AccountLedgers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema; // Ensure this is the class being imported

use App\Models\Branch;
use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Service;

class AccountLedgerForm
{
    /**
     * Updated to accept Schema instead of Form to match the caller.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                    ->live()
                    ->afterStateUpdated(function ($set) {
                        $set('account', null);
                        $set('currency', null);
                        $set('service_id', null);
                    })
                    ->searchable()
                    ->columnSpan(6),

                /* Row 2:  Account(4) | Currency(4) | Service(4) */
              Grid::make(12)
                    ->schema([
        /* 1. Account (Now Live) */
        Select::make('account')
            ->label('Account')
            ->options(function (callable $get) {
                $branchId = $get('branch_id');
                return Accounts::query()
                    ->where('is_active', true)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->get()
                    ->pluck('account_name', 'uid');
            })
            ->live() // Crucial: This triggers the refresh for the Currency field
            ->afterStateUpdated(fn ($set) => $set('currency', null)) // Clear currency if account changes
            ->searchable()
            ->columnSpan(4),

            Select::make('service_id')
            ->label('Service')
            ->options(function (callable $get) {
                $branchId = $get('branch_id');

                if (!$branchId) {
                    return [];
                }

                // 1. Fetch the branch to get its 'active_services' array
                $branch = Branch::find($branchId);

                if (!$branch || empty($branch->active_services)) {
                    return [];
                }

                // 2. Fetch only the services whose IDs are in the branch's active_services array
                return Service::query()
                    ->where('status', true)
                    ->whereIn('id', $branch->active_services) // Use whereIn on the array from Branch
                    ->pluck('title', 'id');
            })
            ->searchable()
            ->columnSpan(4),
                   
        /* 3. Currency (Dependent on Account) */
        Select::make('currency')
            ->label('Currency')
            ->options(function (callable $get) {
                $accountUid = $get('account');

                if (!$accountUid) {
                    return []; // Return empty if no account is selected
                }

                // Fetch the selected account to get its access_currency array
                $account = \App\Models\Accounts::where('uid', $accountUid)->first();

                if (!$account || empty($account->access_currency)) {
                    return [];
                }

                // access_currency is already cast to an array in your Accounts model
                return \App\Models\Currency::query()
                    ->whereIn('id', $account->access_currency)
                    ->where('status', true)
                    ->pluck('currency_name', 'id')
                    ->toArray();
            })
            ->searchable()
            ->columnSpan(4),
    ])
    ->columnSpanFull(),
                /* Row 3: Credit(6) | Debit(6) */
                Grid::make(12)
                    ->schema([
                         DatePicker::make('date_confirm')
                           ->label('Transaction Date')
                            ->default(now())
                            ->columnSpan(4),
                        TextInput::make('debit')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('credit')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(4),

                        
                    ])
                    ->columnSpanFull(), // Forces this Grid to act as a full-width row

                /* Row 4: Description (Full Width) */
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}