<?php

namespace App\Filament\Resources\IncomeLedgers\Tables;

use App\Models\Currency;
use App\Models\Expense;
use App\Models\Income_expense;
use App\Models\Service;
use App\Models\Expense_type;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomeLedgersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Apply base filters from session or request
                $branchId = request()->get('branch_id') ?? Auth::guard('web')->user()->branch_id;
                
                if (Auth::guard('web')->user()->user_type !== "Superuser" && 
                    Auth::guard('web')->user()->user_type !== "Admin") {
                    $query->where('branch_id', Auth::guard('web')->user()->branch_id);
                } elseif ($branchId) {
                    $query->where('branch_id', $branchId);
                }

                // Date range filter
                if (request()->has('from_date') && request()->has('to_date')) {
                    $query->whereBetween('date_update', [
                        request('from_date'), 
                        request('to_date')
                    ]);
                }
            })
            ->columns([
                TextColumn::make('reference_no')
                    ->label('Reference No.')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('service.title')
                    ->label('Service')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('currencyInfo.currency_name')
                    ->label('Currency')
                    ->sortable(),
                
                TextColumn::make('currencyInfo.currency_code')
                    ->label('Code')
                    ->sortable(),
                
                TextColumn::make('credit')
                    ->label('Credit')
                    ->money(fn ($record) => $record->currencyInfo?->currency_code)
                    ->summarize([
                        'sum' => true,
                    ]),
                
                TextColumn::make('debit')
                    ->label('Debit')
                    ->money(fn ($record) => $record->currencyInfo?->currency_code)
                    ->summarize([
                        'sum' => true,
                    ]),
                
                TextColumn::make('balance')
                    ->label('Balance')
                    ->state(fn ($record) => $record->credit - $record->debit)
                    ->money(fn ($record) => $record->currencyInfo?->currency_code),
                
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmed' => 'success',
                        'Pending' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('date_update')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => \App\Models\Branch::pluck('branch_name', 'uid'))
                    ->visible(fn () => Auth::guard('web')->user()->user_type == "Superuser" || 
                                      Auth::guard('web')->user()->user_type == "Admin")
                    ->native(false),
                
                SelectFilter::make('service_uid')
                    ->label('Service')
                    ->options(fn () => Service::pluck('title', 'uid'))
                    ->searchable()
                    ->native(false),
                
                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Confirmed' => 'Confirmed',
                    ])
                    ->native(false),
                
                Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from_date')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('to_date')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_update', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_update', '<=', $date),
                            );
                    }),
                
                Filter::make('time_period')
                    ->form([
                        \Filament\Forms\Components\Select::make('time')
                            ->label('Time Period')
                            ->options([
                                'week' => 'This Week',
                                'month' => 'This Month',
                                'year' => 'This Year',
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['time'])) {
                            return $query;
                        }
                        
                        return match($data['time']) {
                            'week' => $query->whereRaw('WEEK(date_update) = WEEK(NOW())'),
                            'month' => $query->whereRaw('MONTH(date_update) = MONTH(NOW())'),
                            'year' => $query->whereRaw('YEAR(date_update) = YEAR(NOW())'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('print')
                    ->label('Print Report')
                    ->icon(Heroicon::Printer)
                    ->url(fn () => route('print_income_expense', [
                        'from_date' => request('from_date'),
                        'to_date' => request('to_date'),
                        'branch_id' => request('branch_id'),
                    ]))
                    ->openUrlInNewTab(),
                
                \Filament\Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon(Heroicon::DocumentArrowDown)
                    ->color('danger')
                    ->url(fn () => route('income.pdf', [
                        'from_date' => request('from_date'),
                        'to_date' => request('to_date'),
                    ]))
                    ->openUrlInNewTab(),
                
                \Filament\Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('success')
                    ->url(fn () => route('income.exportexcel', [
                        'from_date' => request('from_date'),
                        'to_date' => request('to_date'),
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('date_update', 'desc');
    }

    // Helper method to get income summary by currency (for widgets)
    public static function getIncomeSummaryByCurrency(?string $fromDate = null, ?string $toDate = null, ?string $branchId = null): array
    {
        $query = DB::table('currency')
            ->join('branches', 'currency.branch_id', '=', 'branches.uid')
            ->leftJoin('income_ledger', 'currency.uid', '=', 'income_ledger.currency')
            ->leftJoin('our_service', 'income_ledger.service_uid', '=', 'our_service.uid')
            ->select(
                'currency.uid',
                'currency.currency_name',
                'currency.currency_code',
                'currency.currency_rate',
                'currency.branch_id',
                'branches.branch_name',
                'our_service.title as service_name',
                DB::raw("SUM(IF(income_ledger.status = 'Confirmed', income_ledger.credit, 0)) as totalCredit"),
                DB::raw("SUM(IF(income_ledger.status = 'Confirmed', income_ledger.debit, 0)) as totalDebit")
            )
            ->groupBy('currency.uid', 'currency.currency_name', 'currency.currency_code', 'currency.currency_rate', 'currency.branch_id', 'branches.branch_name', 'our_service.title')
            ->whereNotNull('our_service.title');

        if ($branchId) {
            $query->where('currency.branch_id', $branchId);
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('income_ledger.date_update', [$fromDate, $toDate]);
        }

        return $query->get()->toArray();
    }

    // Helper method to get expense summary by currency
    public static function getExpenseSummaryByCurrency(?string $fromDate = null, ?string $toDate = null, ?string $branchId = null): array
    {
        $query = DB::table('currency')
            ->join('branches', 'currency.branch_id', '=', 'branches.uid')
            ->leftJoin('expenses', 'currency.uid', '=', 'expenses.currency')
            ->leftJoin('expense_type', 'expenses.expense_uid', '=', 'expense_type.uid')
            ->select(
                'currency.uid',
                'currency.currency_name',
                'currency.currency_code',
                'currency.currency_rate',
                'currency.branch_id',
                'branches.branch_name',
                'expense_type.type as expense',
                'expense_type.uid as euid',
                DB::raw("SUM(IF(expenses.status = 'Confirmed', expenses.credit, 0)) as totalCredit"),
                DB::raw("SUM(IF(expenses.status = 'Confirmed', expenses.debit, 0)) as totalDebit")
            )
            ->groupBy('currency.uid', 'currency.currency_name', 'currency.currency_code', 'currency.currency_rate', 'currency.branch_id', 'branches.branch_name', 'expense_type.type')
            ->whereNotNull('expense_type.type');

        if ($branchId) {
            $query->where('currency.branch_id', $branchId);
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('expenses.date_update', [$fromDate, $toDate]);
        }

        return $query->get()->toArray();
    }

    // Get chart data for ApexCharts (stacked bar)
    public static function getChartData(?string $fromDate = null, ?string $toDate = null, ?string $branchId = null): array
    {
        // Income chart data
        $incomeRaw = DB::table('income_ledger')
            ->join('our_service', 'income_ledger.service_uid', '=', 'our_service.uid')
            ->join('currency', 'income_ledger.currency', '=', 'currency.uid')
            ->select(
                'our_service.title as service_name',
                'currency.currency_code',
                DB::raw("SUM(income_ledger.credit - income_ledger.debit) as total_income")
            )
            ->where('income_ledger.status', '=', 'Confirmed')
            ->when($branchId, fn($q) => $q->where('income_ledger.branch_id', $branchId))
            ->when($fromDate && $toDate, fn($q) => $q->whereBetween('income_ledger.date_update', [$fromDate, $toDate]))
            ->groupBy('our_service.title', 'currency.currency_code')
            ->get();

        // Expense chart data
        $expenseRaw = DB::table('expenses')
            ->join('expense_type', 'expenses.expense_uid', '=', 'expense_type.uid')
            ->join('currency', 'expenses.currency', '=', 'currency.uid')
            ->select(
                'expense_type.type as expense_type',
                'currency.currency_code',
                DB::raw("SUM(expenses.debit) as total_expense")
            )
            ->where('expenses.status', '=', 'Confirmed')
            ->when($branchId, fn($q) => $q->where('expenses.branch_id', $branchId))
            ->when($fromDate && $toDate, fn($q) => $q->whereBetween('expenses.date_update', [$fromDate, $toDate]))
            ->groupBy('expense_type.type', 'currency.currency_code')
            ->get();

        // Pivot function
        $pivotData = function ($data, $categoryField, $valueField, $seriesNameField) {
            $categories = $data->pluck($categoryField)->unique()->values();
            $seriesNames = $data->pluck($seriesNameField)->unique()->values();
            
            $series = $seriesNames->map(function ($seriesName) use ($data, $categories, $categoryField, $valueField, $seriesNameField) {
                $categoryData = $categories->map(function ($category) use ($data, $seriesName, $categoryField, $valueField, $seriesNameField) {
                    $record = $data->first(function ($item) use ($category, $seriesName, $categoryField, $seriesNameField) {
                        return $item->{$categoryField} === $category && $item->{$seriesNameField} === $seriesName;
                    });
                    return $record ? (float)$record->{$valueField} : 0;
                });
                return ['name' => $seriesName, 'data' => $categoryData];
            });

            return ['categories' => $categories, 'series' => $series];
        };

        return [
            'income' => $pivotData($incomeRaw, 'service_name', 'total_income', 'currency_code'),
            'expense' => $pivotData($expenseRaw, 'expense_type', 'total_expense', 'currency_code'),
        ];
    }
}