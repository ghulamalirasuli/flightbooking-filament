<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;

// use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

use App\Models\Branch;
use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Service;

use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                   Section::make('Transaction form') // white card background
                    ->schema([
                        Grid::make(12)->schema([

                           Select::make('branch_id')
                            ->label('From Branch')
                            ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                 $set('from_account', null);
                                 $set('to_branch', null); // Reset To Branch if From changes
                                 $set('to_account', null);

                                // $set('account', null);
                                $set('from_currency', null);
                                 $set('service', null);
                            })
                            ->searchable()
                            ->columnSpan(6),
                        Select::make('to_branch')
                            ->label('To Branch')
                            ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('to_account', null);
                                $set('to_currency', null);
                                 $set('service', null);
                            })
                            ->searchable()
                            ->columnSpan(6),
             
                ])->columnSpanFull(),
                             /* Row 2:  Account(4) | Currency(4) | Service(4) */
              Grid::make(12)
                    ->schema([
        /* 1. Account (Now Live) */
             Select::make('from_account')
                ->label('From Account')
                ->options(function (callable $get) {
                    $branchId = $get('branch_id');

                    return \App\Models\Accounts::query()
                        ->with(['accountType', 'branch']) // Eager load for performance
                        ->where('is_active', true)
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->get()
                        ->mapWithKeys(function ($account) {
                            $name = $account->account_name;
                            $category = $account->accountType?->accounts_category ?? 'N/A';
                            $branch = $account->branch?->branch_name ?? 'N/A';

                            return [
                                $account->uid => "({$branch}) {$name} - {$category}"
                            ];
                        });
                })
                ->live()
                ->afterStateUpdated(function ($set, $get) {
                    $set('to_account', null); // Clear the "To Account" whenever "From Account" is updated
                    $set('from_currency', null);
                })
                ->searchable()
                ->columnSpan(6),
                   
                        /* 1. Account (Now Live) */
     Select::make('to_account')
    ->label('To Account')
    ->options(function (callable $get) {
        $branchId = $get('to_branch');
        $fromAccount = $get('from_account');

        return \App\Models\Accounts::query()
            ->with(['accountType', 'branch']) // Eager load for performance
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($fromAccount, fn ($q) => $q->where('uid', '!=', $fromAccount)) // Exclude the "From Account"
            ->get()
            ->mapWithKeys(function ($account) {
                $name = $account->account_name;
                $category = $account->accountType?->accounts_category ?? 'N/A';
                $branch = $account->branch?->branch_name ?? 'N/A';

                return [
                    $account->uid => "({$branch}) {$name} - {$category}"
                ];
            });
    })
    ->live()
    ->afterStateUpdated(fn ($set) => $set('to_currency', null))
    ->searchable()
    ->columnSpan(6),
    
    ])
    ->columnSpanFull(),
                 /* Row 3:  Account(4) | Currency(4) | Service(4) */
              Grid::make(12)
                    ->schema([

        /* 3. Currency (Dependent on Account) */
        Select::make('from_currency')
    ->label('From Currency')
    ->options(function (callable $get) {
        $accountUid = $get('from_account');

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
    ->columnSpan(6),
        /* 3. Currency (Dependent on Account) */
        Select::make('to_currency')
    ->label('To Currency')
    ->options(function (callable $get) {
        $toAccountUid = $get('to_account');

        if (!$toAccountUid) {
            return []; // Return empty if no account is selected
        }

        // Fetch the selected account to get its access_currency array
        $account = \App\Models\Accounts::where('uid', $toAccountUid)->first();

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
    ->columnSpan(6),
    ])
    ->columnSpanFull(),

             Grid::make(12)
                    ->schema([
                    Select::make('service')
                    ->label('Service')
                    ->options(function (callable $get) {
                        $branchId = $get('branch_id');

                        if (!$branchId) {
                            return []; // Return empty if no branch is selected
                        }

                        // Fetch the selected branch to get its active_services array
                        $branch = \App\Models\Branch::where('id', $branchId)->first();

                        if (!$branch || empty($branch->active_services)) {
                            return [];
                        }

                        // active_services is already cast to an array in your Branch model
                        return \App\Models\Service::query()
                            ->whereIn('id', $branch->active_services)
                            ->where('status', true)
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->columnSpan(4),

                    DateTimePicker::make('date_remind')->columnSpan(2),
                    DatePicker::make('delivery_date')->columnSpan(2),
                    DatePicker::make('depart_date')->columnSpan(2),
                    DatePicker::make('arrival_date')->columnSpan(2),
                        
                    ])
                    ->columnSpanFull(), // Forces this Grid to act as a full-width row

                /* Row 4: Description (Full Width) */
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                    ])->columnSpanFull(),// to make form max in width
            ]);
    }
}
