<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Pages\Page;
use App\Models\Branch;
use App\Models\Accounts;
use App\Models\Account_ledger;
use App\Models\Currency;
use Filament\Forms\Components\Select;

use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AccountBalance extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.account-balance';
    public ?array $data = [];

    public function form(Schema $form): Schema 
    {
        return $form
            ->schema([
                Section::make('Account Selection')
                    ->schema([
                        Select::make('selectedBranch')
                            ->label('Branch')
                            ->options(Branch::all()->pluck('branch_name', 'id'))
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('selectedAccount', null)),

                        Select::make('selectedAccount')
                            ->label('Account')
                            ->options(function ($get) {
                                $branchId = $get('selectedBranch');
                                if (!$branchId) return [];
                                return Accounts::where('branch_id', $branchId)
                                    ->get()
                                    ->pluck('account_name_with_category_and_branch', 'id');
                            })
                            ->required()
                            ->live(), 
                    ])
            ])
            ->statePath('data');
    }

    public function getAccountBalancesProperty(): Collection
    {
        $accountId = $this->data['selectedAccount'] ?? null;
        if (!$accountId) return collect();

        $account = Accounts::find($accountId);
        if (!$account) return collect();

        // 1. Get all available currencies to ensure 0 balances show up
        $allCurrencies = Currency::all();

        // 2. Get actual ledger sums grouped by currency and status
        $ledgerTotals = Account_ledger::where('account', $account->uid)
            ->select('currency', 'status', DB::raw('SUM(credit) - SUM(debit) as net_balance'))
            ->groupBy('currency', 'status')
            ->get();

        $results = collect();

        // 3. Force both "Confirmed" and "Pending" rows for every currency
        foreach ($allCurrencies as $currency) {
            foreach (['Confirmed', 'Pending'] as $status) {
                $match = $ledgerTotals->where('currency', $currency->id)->where('status', $status)->first();
                
                $results->push([
                    'currency_name' => $currency->currency_name,
                    'currency_code' => $currency->currency_code,
                    'status' => $status,
                    'balance' => $match ? $match->net_balance : 0, // Default to 0 if no record
                ]);
            }
        }

        return $results;
    }
}