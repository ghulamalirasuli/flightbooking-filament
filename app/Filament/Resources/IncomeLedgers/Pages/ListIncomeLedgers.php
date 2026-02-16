<?php

namespace App\Filament\Resources\IncomeLedgers\Pages;

use App\Filament\Resources\IncomeLedgerResource;
use App\Filament\Resources\IncomeLedgers\Tables\IncomeLedgersTable;
use App\Models\Currency;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ListIncomeLedgers extends ListRecords
{
    protected static string $resource = IncomeLedgerResource::class;

    // Include chart widgets
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\IncomeExpenseChart::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    // Custom content for summary tables below the main table
    protected function getFooterSchema(): ?Schema
    {
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $branchId = request('branch_id') ?? auth()->user()->branch_id;

        $incomeData = IncomeLedgersTable::getIncomeSummaryByCurrency($fromDate, $toDate, $branchId);
        $expenseData = IncomeLedgersTable::getExpenseSummaryByCurrency($fromDate, $toDate, $branchId);
        
        // Calculate totals
        $incomeTotals = [];
        foreach ($incomeData as $item) {
            $balance = $item->totalCredit - $item->totalDebit;
            if (!isset($incomeTotals[$item->uid])) {
                $incomeTotals[$item->uid] = [
                    'currency_name' => $item->currency_name,
                    'currency_code' => $item->currency_code,
                    'currency_rate' => $item->currency_rate,
                    'total' => 0,
                ];
            }
            $incomeTotals[$item->uid]['total'] += $balance;
        }

        $expenseTotals = [];
        foreach ($expenseData as $item) {
            $balance = $item->totalCredit - $item->totalDebit;
            if (!isset($expenseTotals[$item->uid])) {
                $expenseTotals[$item->uid] = [
                    'currency_name' => $item->currency_name,
                    'currency_code' => $item->currency_code,
                    'currency_rate' => $item->currency_rate,
                    'total' => 0,
                ];
            }
            $expenseTotals[$item->uid]['total'] += $balance;
        }

        // Net balance calculation
        $allCurrencyIds = array_unique(array_merge(array_keys($incomeTotals), array_keys($expenseTotals)));
        $netBalances = [];
        $totalInDefault = 0;
        $defaultCurrency = Currency::where('defaults', 1)
            ->where('branch_id', $branchId)
            ->first();

        foreach ($allCurrencyIds as $uid) {
            $income = $incomeTotals[$uid]['total'] ?? 0;
            $expense = $expenseTotals[$uid]['total'] ?? 0;
            $net = $income + $expense; // Expense is negative
            $rate = ($incomeTotals[$uid]['currency_rate'] ?? $expenseTotals[$uid]['currency_rate']) ?: 1;
            
            $netBalances[] = [
                'currency_name' => $incomeTotals[$uid]['currency_name'] ?? $expenseTotals[$uid]['currency_name'],
                'currency_code' => $incomeTotals[$uid]['currency_code'] ?? $expenseTotals[$uid]['currency_code'],
                'rate' => $rate,
                'income' => $income,
                'expense' => $expense,
                'net' => $net,
                'net_in_default' => $net / $rate,
            ];
            
            $totalInDefault += ($net / $rate);
        }

        return Schema::make([
            Section::make('Summary Reports')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            // Income Summary Table
                            Section::make('Total Income by Currency')
                                ->color('success')
                                ->schema([
                                    \Filament\Schemas\Components\View::make('filament.tables.income-summary')
                                        ->viewData(['totals' => $incomeTotals]),
                                ]),
                            
                            // Expense Summary Table
                            Section::make('Total Expense by Currency')
                                ->color('danger')
                                ->schema([
                                    \Filament\Schemas\Components\View::make('filament.tables.expense-summary')
                                        ->viewData(['totals' => $expenseTotals]),
                                ]),
                        ]),
                    
                    // Net Balance Table
                    Section::make('Net Balance per Currency')
                        ->color('primary')
                        ->schema([
                            \Filament\Schemas\Components\View::make('filament.tables.net-balance')
                                ->viewData([
                                    'balances' => $netBalances,
                                    'defaultCurrency' => $defaultCurrency,
                                    'totalInDefault' => $totalInDefault,
                                ]),
                        ]),
                ]),
        ]);
    }
}