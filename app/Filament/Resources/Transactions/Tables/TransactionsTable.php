<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use Filament\Actions\RestoreAction;        
use Filament\Actions\RestoreBulkAction;    
use Filament\Actions\ForceDeleteAction;    
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\BulkAction;

use Filament\Tables\Table;
use Filament\Tables\Enums\ColumnManagerResetActionPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;


use App\Models\Account_ledger;
use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\CashBox;
use App\Models\AddTransaction;
use App\Models\Service;
use App\Models\User;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;

use Filament\Notifications\Notification;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                    TextColumn::make('user.name')
                // ->label('User / Inserted At')
                ->label('Inserted')
                ->description(fn ($record): string => $record->created_at?->format('M d, Y H:i') ?? 'N/A')
                ->searchable(),

                 TextColumn::make('reference_no')
                    ->label('Reference No.')
                    ->searchable(),

                 TextColumn::make('description')
                    ->label('Description')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('service.title')
                ->label('Service')->searchable(),



            TextColumn::make('account_from')
                ->label('From Account')
                // 1. Main Line: The Account Name
                ->formatStateUsing(fn ($record) => $record->accountFrom?->account_name_with_category_and_branch ?? 'N/A')
                // 2. Second Line (Description): The Amount and Currency
                ->description(function ($record) {
                    $amount = $record->fixed_price ?? '0';
                    $currency = $record->currencyFrom?->currency_code ?? '';
                    $from_pay = $record->from_pay_status ?? '-';
                    
                    return "$amount $currency ($from_pay)"; // Example Output: "500 USD (Invoice)"
                })
                ->searchable(),

            TextColumn::make('account_to')
                ->label('To Account')
                // 1. Main Line: The Account Name
                ->formatStateUsing(fn ($record) => $record->accountTo?->account_name_with_category_and_branch ?? 'N/A')
                // 2. Second Line (Description): The Amount and Currency
                ->description(function ($record) {
                    $amount = $record->sold_price ?? '0';
                    $currency = $record->currencyTo?->currency_code ?? '';
                    $to_pay = $record->to_pay_status ?? '-';    
                    
                    return "$amount $currency ($to_pay)"; // Example Output: "500 USD (Cash)"
                })
                ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge() // Optional: makes the status look like a pill
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmed' => 'success',
                        'Pending' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->description(function ($record): ?string {
                        // Check if the relationship exists and has a name
                        if (!$record->updated_by || !$record->updated_by->name) {
                            return null;
                        }

                        $date = $record->updated_at?->format('M d, Y H:i') ?? 'N/A';
                        $userName = $record->updated_by->name;

                        return "{$date} By {$userName}";
                    }),

    TextColumn::make('profit')
    ->label('Profit')
    ->formatStateUsing(function ($record) {
        return number_format($record->profit ?? 0, 2)
            . '<br><span class="text-gray-500 text-sm">'
            . ($record->profitCurrency?->currency_code ?? '-')
            . '</span>';
    })
    ->html()
    ->summarize(
    Summarizer::make()
        ->label('Total Profit')
        ->using(function ($query): array {
            $total = $query->sum('profit');
            
            // Get distinct non-null currencies only
            $currencyIds = $query->clone()
                ->whereNotNull('default_currency')
                ->distinct()
                ->pluck('default_currency');
            
            if ($currencyIds->count() === 1) {
                $currency = \App\Models\Currency::find($currencyIds->first())?->currency_code ?? '-';
            } elseif ($currencyIds->isEmpty()) {
                $currency = '-'; // No currency set
            } else {
                $currency = 'Mixed';
            }
            
            return [
                'total' => $total,
                'currency' => $currency,
            ];
        })
        ->formatStateUsing(fn (array $state): string => 
            number_format($state['total'], 2) . ' ' . $state['currency']
        )
)->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])->defaultSort('created_at', 'desc')
            ->filters([
                // ===============================
                // CUSTOM FILTER FORM WITH DEPENDENT DROPDOWNS
                // ===============================
                Filter::make('filters')
                    ->form([
                        // BRANCH SELECTOR
                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::pluck('branch_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live() // This makes it reactive
                            ->columnSpan(3),

                        // ACCOUNT SELECTOR - Depends on Branch
                        Select::make('account')
                            ->label('Account')
                            ->options(function (callable $get) {
                                $branchId = $get('branch_id');
                                
                                if (!$branchId) {
                                    return [];
                                }
                                
                                return Accounts::where('branch_id', $branchId)
                                    ->get()
                                    ->mapWithKeys(fn($account) => [
                                        $account->uid => $account->account_name_with_category_and_branch
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => !$get('branch_id'))
                            ->columnSpan(3),

                        // USER SELECTOR - Depends on Branch
                        Select::make('user_id')
                            ->label('User')
                            ->options(function (callable $get) {
                                $branchId = $get('branch_id');
                                
                                if (!$branchId) {
                                    return [];
                                }
                                
                                return User::where('branch_id', $branchId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => !$get('branch_id'))
                            ->columnSpan(3),

                        // SERVICE SELECTOR - All services (no branch filter in Service model)
                        Select::make('service_type')
                            ->label('Service')
                            ->options(Service::pluck('title', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(3),

                        // DATE RANGE
                        DatePicker::make('from')
                            ->label('From Date')
                            ->columnSpan(3),
                            
                        DatePicker::make('until')
                            ->label('To Date')
                            ->columnSpan(3),

                        // STATUS - Column span 2 to make room for Trashed beside it
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Confirmed' => 'Confirmed',
                                'Pending'   => 'Pending',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Pending')
                            ->columnSpan(3),

                        // TRASHED FILTER - Now beside Status (column span 2 + 2 = 4, fits in 12 column grid)
                        Select::make('trashed')
                            ->label('Deleted records')
                            ->options([
                                '' => 'Without deleted records',
                                'with' => 'With deleted records',
                                'only' => 'Only deleted records',
                            ])
                            ->default('')
                            ->columnSpan(3),
                    ])
                    ->columns(12)
                    ->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            // Branch filter
                            ->when(
                                $data['branch_id'] ?? null,
                                fn ($q, $value) => $q->where('branch_id', $value)
                            )
                            // Account filter (checks both account_from and account_to)
                            ->when(
                                $data['account'] ?? null,
                                fn ($q, $value) => $q->where(function ($subQuery) use ($value) {
                                    $subQuery->where('account_from', $value)
                                             ->orWhere('account_to', $value);
                                })
                            )
                            // User filter
                            ->when(
                                $data['user_id'] ?? null,
                                fn ($q, $value) => $q->where('user_id', $value)
                            )
                            // Service filter
                            ->when(
                                $data['service_type'] ?? null,
                                fn ($q, $value) => $q->where('service_type', $value)
                            )
                            // Status filter
                            ->when(
                                $data['status'] ?? null,
                                fn ($q, $value) => $q->where('status', $value)
                            )
                            // Date range filters
                            ->when(
                                $data['from'] ?? null,
                                fn ($q, $date) => $q->whereNotNull('date_confirm')
                                                    ->whereDate('date_confirm', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn ($q, $date) => $q->whereNotNull('date_confirm')
                                                    ->whereDate('date_confirm', '<=', $date)
                            )
                            // Trashed filter logic
                            ->when(
                                isset($data['trashed']) && $data['trashed'] === 'with',
                                fn ($q) => $q->withTrashed()
                            )
                            ->when(
                                isset($data['trashed']) && $data['trashed'] === 'only',
                                fn ($q) => $q->onlyTrashed()
                            );
                    })
                    // ADD THIS: Define filter indicators
    ->indicateUsing(function (array $data): array {
        $indicators = [];
        
        if ($data['branch_id'] ?? null) {
            $branch = Branch::find($data['branch_id']);
            $indicators[] = \Filament\Tables\Filters\Indicator::make('Branch: ' . ($branch?->branch_name ?? 'Unknown'))
                ->removeField('branch_id');
        }
        
        if ($data['account'] ?? null) {
            $account = Accounts::where('uid', $data['account'])->first();
            $indicators[] = \Filament\Tables\Filters\Indicator::make('Account: ' . ($account?->account_name ?? 'Unknown'))
                ->removeField('account');
        }
        
        if ($data['user_id'] ?? null) {
            $user = User::find($data['user_id']);
            $indicators[] = \Filament\Tables\Filters\Indicator::make('User: ' . ($user?->name ?? 'Unknown'))
                ->removeField('user_id');
        }
        
        if ($data['service_type'] ?? null) {
            $service = Service::find($data['service_type']);
            $indicators[] = \Filament\Tables\Filters\Indicator::make('Service: ' . ($service?->title ?? 'Unknown'))
                ->removeField('service_type');
        }
        
        if ($data['status'] ?? null) {
            $indicators[] = \Filament\Tables\Filters\Indicator::make('Status: ' . $data['status'])
                ->removeField('status');
        }
        
        if (($data['from'] ?? null) || ($data['until'] ?? null)) {
            $from = $data['from'] ?? 'Start';
            $until = $data['until'] ?? 'End';
            $indicators[] = \Filament\Tables\Filters\Indicator::make("Date: {$from} to {$until}")
                ->removeField(['from', 'until']);
        }
        
        if (($data['trashed'] ?? '') !== '') {
            $trashedLabels = [
                'with' => 'With deleted',
                'only' => 'Only deleted',
            ];
            $indicators[] = \Filament\Tables\Filters\Indicator::make($trashedLabels[$data['trashed']] ?? 'Unknown')
                ->removeField('trashed');
        }
        
        return $indicators;
    }),
            ], FiltersLayout::Modal)
            ->deferFilters(false)
            ->filtersFormColumns(12)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->filtersFormWidth(Width::Full)
            ->recordActions([
               ActionGroup::make([
                ViewAction::make()
                    ->openUrlInNewTab(),
                EditAction::make(),
                 DeleteAction::make(),
               ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}