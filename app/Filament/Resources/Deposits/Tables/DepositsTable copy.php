<?php

namespace App\Filament\Resources\Deposits\Tables;

use Filament\Actions\CreateAction;

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


use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Group;

use Filament\Tables\Table;
use Filament\Tables\Enums\ColumnManagerResetActionPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;

use App\Models\Account_ledger;
use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\CashBox;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use Filament\Notifications\Notification;

class DepositsTable
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
                ->searchable()
                ->sortable(),
                

                 TextColumn::make('reference_no')
                    ->label('Reference No.')
                    ->searchable(),

                    TextColumn::make('description')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                    
                TextColumn::make('account.account_name')
                    ->label('Account')
                    ->formatStateUsing(function ($record) {
                        $account = $record->account;
                        
                        if (!$account) {
                            return 'N/A';
                        }

                        // Accessing data across the three models
                        $name = $account->account_name; // From Accounts model
                        $category = $account->accountType?->accounts_category ?? 'No Category'; // From Account_category model
                        $branch = $account->branch?->branch_name ?? 'No Branch'; // From Branch model

                        return "{$name} - {$category} ({$branch})";
                    })
                    // Ensure searching still works on the account name and related fields
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('account', function ($q) use ($search) {
                            $q->where('account_name', 'like', "%{$search}%")
                            ->orWhereHas('accountType', fn($inner) => $inner->where('accounts_category', 'like', "%{$search}%"))
                            ->orWhereHas('branch', fn($inner) => $inner->where('branch_name', 'like', "%{$search}%"));
                        });
                    }),

                // TextColumn::make('entry_type')
                //     ->label('Type')
                //     ->searchable(),

                // TextColumn::make('amount_from')
                //     ->label('Amount')
                //     ->searchable(),

                //   TextColumn::make('currency.currency_name')
                //     ->label('Currency')
                //     ->searchable(),

                // First Column
                TextColumn::make('debit_display') // Unique identifier
                    ->label('Debit')
                    ->state(fn ($record): string => $record->currency?->currency_name ?? '') 
                    ->description(fn ($record): string => $record->debit ?? '0'),

                // Second Column
                TextColumn::make('credit_display') // Unique identifier
                    ->label('Credit')
                    ->state(fn ($record): string => $record->currency?->currency_name ?? '')
                    ->description(fn ($record): string => $record->credit ?? '0'),

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
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
        ])
            ->headerActions([

                // Add the Exchange action here
Action::make('exchange')
    ->label('Currency Exchange')
    ->icon('heroicon-o-arrows-right-left')
    ->color('info')
    ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
    ->form([
        Section::make()
            ->schema([
                Grid::make(12)->schema([
                    // 1. Branch Selection
                    Select::make('branch_id')
                        ->label('Branch')
                        ->options(\App\Models\Branch::where('status', true)->pluck('branch_name', 'id'))
                        ->live()
                        ->required()
                        ->columnSpan(12),

                    // 2. Sell Currency
                    Select::make('sell_currency')
                        ->label('Sell Currency')
                        ->live()
                        ->required()
                        ->options(fn (callable $get) => 
                            \App\Models\Currency::where('status', true)->pluck('currency_name', 'id')
                        )
                        ->columnSpan(4),

                    // 3. Operator (The "Action" in your blade file)
                    Select::make('divmul')
                        ->label('Action')
                        ->options([
                            'Multiply' => 'Multiply (*)',
                            'Divide' => 'Divide (/)',
                        ])
                        ->default('Multiply')
                        ->live() // Essential for immediate calculation
                        ->required()
                        ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::updateExchangeAmounts($get, $set))
                        ->columnSpan(4),

                    // 4. Buy Currency
                    Select::make('buy_currency')
                        ->label('Buy Currency')
                        ->live()
                        ->required()
                        ->options(fn (callable $get) => 
                            \App\Models\Currency::where('status', true)
                                ->where('id', '!=', $get('sell_currency'))
                                ->pluck('currency_name', 'id')
                        )
                        ->columnSpan(4),

                    // 5. Sell Amount (Debit)
                    TextInput::make('sell_amount')
                        ->label('Sell Amount (Debit)')
                        ->numeric()
                        ->required()
                        ->live(onBlur: true) // Calculates when user clicks away or stops typing
                        ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::updateExchangeAmounts($get, $set))
                        ->columnSpan(4),

                    // 6. Rate
                    TextInput::make('rate')
                        ->label('Exchange Rate')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::updateExchangeAmounts($get, $set))
                        ->columnSpan(4),

                    // 7. Buy Amount (Credit)
                    TextInput::make('buy_amount')
                        ->label('Buy Amount (Credit)')
                        ->numeric()
                        ->required()
                        ->placeholder('Calculated automatically...')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::updateReverseExchange($get, $set))
                        ->columnSpan(4),

                    Textarea::make('description')->columnSpanFull(),
                ]),
            ])
    ])
    ->action(function (array $data) {
        // Process logic...
        Notification::make()->title('Exchange processed')->success()->send();
    }),
            // Your existing New Deposit action

                Action::make('create_deposit')
                    ->label('New Deposit')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('New Deposit')
                    ->form([
                        // ROW 1: Branch and Date
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
                                ->columnSpan(6),
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
                                ->required()
                                // ->helperText('Select the source account for this deposit. Only active accounts for the selected branch are shown.')
                                ->afterStateUpdated(fn ($set) => $set('currency', null))
                                ->searchable()
                                ->columnSpan(6),
                        ]),

                        // ROW 2: Account and Currency (DEPENDENT)
                        Grid::make(12)->schema([
                            

                            Select::make('entry_type')
                            ->label('Deposit Type')
                            ->options([
                                'Debit' => 'Debit',
                                'Credit' => 'Credit',
                            ])->required()->columnSpan(4),

                       Select::make('currency_id')
                                ->label('Currency')
                                ->options(function (callable $get) {
                                    $accountUid = $get('from_account');
                                    if (!$accountUid) return [];
                                    
                                    $account = Accounts::where('uid', $accountUid)->first();
                                    if (!$account || empty($account->access_currency)) return [];

                                    return Currency::query()
                                        ->whereIn('id', $account->access_currency)
                                        ->where('status', true)
                                        ->pluck('currency_name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->required()
                                ->noSearchResultsMessage('No currency found for this account.')
                                ->columnSpan(4),

                        TextInput::make('amount_from')
                            ->label("Amount")
                            ->required()
                            ->numeric()
                            ->columnSpan(4),
                        ]),

                        // ROW 4: Description
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                    ])
                                   ->action(function (array $data) {
                        // Insert into cash_box table
                        $cashBox = CashBox::create([
                            'uid' => 'CBI' . now()->format('ymdhis'),
                            'from_account' => $data['from_account'],
                            'amount_from' => $data['amount_from'],
                            'currency_from' => $data['currency_id'],
                            'reference_no' => 'CBR' . now()->format('ymdhis'),
                            'reference' => 'CB' . now()->format('ymdhis'),
                            'credit' => $data['entry_type'] === 'Credit' ? 0 : $data['amount_from'],
                            'debit' => $data['entry_type'] === 'Debit' ? 0 :  $data['amount_from'],
                            'description' => $data['description'],
                            'currency_id' => $data['currency_id'],
                            'entry_type' => $data['entry_type'],
                            'branch_id' => $data['branch_id'],
                            'user_id' => auth()->id(),
                            'date_confirm' => now()->format('Y-m-d'),
                            'date_update' => now()->format('Y-m-d'),
                        ]);

                        // Insert into account_ledger table
                        Account_ledger::create([
                            'uid' => 'CBI' . now()->format('ymdhis'),
                            'account' => $data['from_account'],
                            'reference_no' => 'CBR' . now()->format('ymdhis'),
                            'reference' => 'CB' . now()->format('ymdhis'),
                            'description' => $data['description'],
                            'credit' => $data['entry_type'] === 'Credit' ? $data['amount_from'] : 0,
                            'debit' => $data['entry_type'] === 'Debit' ? $data['amount_from'] : 0,
                            'currency' => $data['currency_id'],
                            'user_id' => auth()->id(),
                            'branch_id' => $data['branch_id'],
                            'date_confirm' => now()->format('Y-m-d'),
                            'date_update' => now()->format('Y-m-d'),
                            'pay_status' =>'Cash'
                        ]);

                        Notification::make()->title('Deposit Saved')->success()->send();
                    })
            ])->deferColumnManager(false)
            ->columnManagerResetActionPosition(ColumnManagerResetActionPosition::Footer)

            ->action(function (array $data): void {
    \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
        $reference_no = 'EXC-' . strtoupper(bin2hex(random_bytes(4)));
        $uid = (string) \Illuminate\Support\Str::uuid();

        // 1. STORE THE SELL ENTRY (DEBIT)
        \App\Models\CashBox::create([
            'uid'           => $uid,
            'reference_no'  => $reference_no,
            'branch_id'     => $data['branch_id'],
            'user_id'       => auth()->id(),
            'currency_id'   => $data['sell_currency'], // The currency being sold
            'debit'         => $data['sell_amount'],    // Money going out
            'credit'        => 0,
            'entry_type'    => 'Exchange-Sell',
            'description'   => $data['description'] ?? 'Currency Exchange Sell',
            'status'        => 1,
            'date_confirm'  => now(),
        ]);

        // 2. STORE THE BUY ENTRY (CREDIT)
        \App\Models\CashBox::create([
            'uid'           => (string) \Illuminate\Support\Str::uuid(),
            'reference_no'  => $reference_no, // Use same Ref No to link them
            'branch_id'     => $data['branch_id'],
            'user_id'       => auth()->id(),
            'currency_id'   => $data['buy_currency'],  // The currency being bought
            'debit'         => 0,
            'credit'        => $data['buy_amount'],     // Money coming in
            'entry_type'    => 'Exchange-Buy',
            'description'   => "Exchange Rate: {$data['rate']} ({$data['divmul']})",
            'status'        => 1,
            'date_confirm'  => now(),
        ]);
    });

    \Filament\Notifications\Notification::make()
        ->title('Exchange Completed successfully')
        ->success()
        ->send();
})

            ->filters([
             Filter::make('date_range')
                    ->schema([
                        DatePicker::make('date_confirm_from')->label('From Date'),
                        DatePicker::make('date_confirm_until')->label('Until Date'),
                    ])
                    ->columns(2)
                    ->columnSpan(4)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_confirm_from'], fn ($q, $date) => $q->whereDate('date_confirm', '>=', $date))
                            ->when($data['date_confirm_until'], fn ($q, $date) => $q->whereDate('date_confirm', '<=', $date));
                    }),
                TrashedFilter::make()->columnSpan(2),
                SelectFilter::make('currency_id')
                    ->label('Currency')
                    ->options(function () {
                        return \App\Models\Currency::query()
                            ->where('status', true)
                            ->pluck('currency_name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->columnSpan(2),
                    SelectFilter::make('status')
                        ->options([
                            'Confirmed' => 'Confirmed',
                            'Pending' => 'Pending',
                            'Cancelled' => 'Cancelled',
                        ])
                        ->default('Pending')// Sets the default state to Pending
                        ->columnSpan(2),
                   SelectFilter::make('entry_type')
                        ->options([
                            'Debit' => 'Debit',
                            'Credit' => 'Credit',
                        ])
                    ->default('Pending') // Sets the default state to Pending
                    ->columnSpan(2)
            ], FiltersLayout::Modal)
            ->deferFilters(false)
            ->filtersFormColumns(12)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->filtersFormWidth(Width::Full)
            ->recordActions([
               ActionGroup::make([
                        
                // ---PDF  PRINT ACTION ---
        // Action::make('print')
        //     ->label('Print Receipt')
        //     ->icon('heroicon-m-printer')
        //     ->color('info')
        //     ->action(function ($record) {
        //         // Load a blade view and pass the record data
        //         $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('print.deposit-pdf', [
        //             'record' => $record,
        //         ]);

        //         // Download the file
        //         return response()->streamDownload(function () use ($pdf) {
        //             echo $pdf->output();
        //         }, "Deposit_{$record->reference_no}.pdf");
        //     }),
                // --- CONFIRM ACTION ---
        Action::make('confirm')
            ->label('Confirm')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn ($record) => $record->status !== 'Confirmed')
            ->action(function ($record) {
                // 1. Update the CashBox record
                $record->update(['status' => 'Confirmed','update_by' => auth()->id()]);

                // 2. Update the related Account Ledger record
                Account_ledger::where('reference_no', $record->reference_no)
                    ->update(['status' => 'Confirmed']);

                Notification::make()->title('Deposit Confirmed')->success()->send();
            }),

        // --- CANCEL ACTION ---
        Action::make('cancel')
            ->label('Cancel')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn ($record) => $record->status !== 'Cancelled')
            ->action(function ($record) {
                // 1. Update the CashBox record
                $record->update(['status' => 'Cancelled','update_by' => auth()->id()]);

                // 2. Update the related Account Ledger record
                Account_ledger::where('reference_no', $record->reference_no)
                    ->update(['status' => 'Cancelled']);

                Notification::make()->title('Deposit Cancelled')->danger()->send();
            }),

        // --- SET PENDING ACTION ---
        Action::make('setPending')
            ->label('Mark as Pending')
            ->icon('heroicon-m-pause-circle')
            ->color('gray')
            ->visible(fn ($record) => $record->status !== 'Pending')
            ->action(function ($record) {
                // 1. Update the CashBox record
                $record->update(['status' => 'Pending','update_by' => auth()->id()]);

                // 2. Update the related Account Ledger record
                Account_ledger::where('reference_no', $record->reference_no)
                    ->update(['status' => 'Pending']);

                Notification::make()->title('Deposit set to Pending')->info()->send();
            }),

            // --------- Print-----------
            Action::make('print')
            ->label('Print')
            ->icon('heroicon-m-printer')
            ->color('info')
            // Pass the single ID as 'record'
            ->url(fn ($record) => route('deposits.print', ['record' => $record->id]))
            ->openUrlInNewTab(),

                    // ViewAction::make(),
                    // EditAction::make(),
            ViewAction::make(), 
        
        EditAction::make()
            ->modalHeading('Edit Deposit'),
                   // SYNCED DELETE
        DeleteAction::make()
            ->action(function ($record) {
                DB::transaction(function () use ($record) {
                    // Soft delete the ledger entry
                    Account_ledger::where('reference_no', $record->reference_no)->delete();
                    // Soft delete the main record
                    $record->delete();
                });
                Notification::make()->title('Deposit and Ledger moved to trash')->success()->send();
            }),

        // SYNCED RESTORE
        RestoreAction::make()
            ->action(function ($record) {
                DB::transaction(function () use ($record) {
                    // Restore the ledger entry (using withTrashed to find it)
                    Account_ledger::withTrashed()
                        ->where('reference_no', $record->reference_no)
                        ->restore();
                    // Restore the main record
                    $record->restore();
                });
                Notification::make()->title('Deposit and Ledger restored')->success()->send();
            }),

        // SYNCED FORCE DELETE
        ForceDeleteAction::make()
            ->label('Delete forever')
            ->action(function ($record) {
                DB::transaction(function () use ($record) {
                    // Permanently delete the ledger entry
                    Account_ledger::withTrashed()
                        ->where('reference_no', $record->reference_no)
                        ->forceDelete();
                    // Permanently delete the main record
                    $record->forceDelete();
                });
                Notification::make()->title('Deposit and Ledger deleted permanently')->danger()->send();
            }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
// 1. BULK PENDING
        BulkAction::make('bulk_pending')
            ->label('Mark as Pending')
            ->icon('heroicon-m-pause-circle')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    $refs = $records->pluck('reference_no')->toArray();

                    // Update CashBox records
                    $records->each->update(['status' => 'Pending','update_by' => auth()->id()]);

                    // Update corresponding Ledgers
                    \App\Models\Account_ledger::whereIn('reference_no', $refs)
                        ->update(['status' => 'Pending']);
                });
                
                \Filament\Notifications\Notification::make()
                    ->title('Selected deposits set to Pending')
                    ->info()
                    ->send();
            }),

        // 2. BULK CONFIRM
        BulkAction::make('bulk_confirm')
            ->label('Confirm Selected')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    $refs = $records->pluck('reference_no')->toArray();

                    $records->each->update(['status' => 'Confirmed','update_by' => auth()->id()]);

                    \App\Models\Account_ledger::whereIn('reference_no', $refs)
                        ->update(['status' => 'Confirmed']);
                });
                
                \Filament\Notifications\Notification::make()
                    ->title('Selected deposits confirmed')
                    ->success()
                    ->send();
            }),

        // 3. BULK CANCEL
        BulkAction::make('bulk_cancel')
            ->label('Cancel Selected')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    $refs = $records->pluck('reference_no')->toArray();

                    $records->each->update(['status' => 'Cancelled','update_by' => auth()->id()]);

                    \App\Models\Account_ledger::whereIn('reference_no', $refs)
                        ->update(['status' => 'Cancelled']);
                });
                
                \Filament\Notifications\Notification::make()
                    ->title('Selected deposits cancelled')
                    ->danger()
                    ->send();
            }),

      // SYNCED BULK DELETE
        DeleteBulkAction::make()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    $refs = $records->pluck('reference_no')->toArray();
                    
                    // Bulk soft delete ledgers
                    Account_ledger::whereIn('reference_no', $refs)->delete();
                    // Bulk soft delete main records
                    $records->each->delete();
                });
                Notification::make()->title('Selected records moved to trash')->success()->send();
            }),

        // SYNCED BULK RESTORE
        RestoreBulkAction::make()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    $refs = $records->pluck('reference_no')->toArray();

                    // Bulk restore ledgers
                    Account_ledger::withTrashed()
                        ->whereIn('reference_no', $refs)
                        ->restore();
                    // Bulk restore main records
                    $records->each->restore();
                });
                Notification::make()->title('Selected records restored')->success()->send();
            }),

        // SYNCED BULK FORCE DELETE
        ForceDeleteBulkAction::make()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    $refs = $records->pluck('reference_no')->toArray();

                    // Bulk permanent delete ledgers
                    Account_ledger::withTrashed()
                        ->whereIn('reference_no', $refs)
                        ->forceDelete();
                    // Bulk permanent delete main records
                    $records->each->forceDelete();
                });
                Notification::make()->title('Selected records deleted permanently')->danger()->send();
            }),

    //         BulkAction::make('bulk_print')
    // ->label('Print Selected (A4)')
    // ->icon('heroicon-m-printer')
    // ->color('info')
    // ->action(function (Collection $records) {
    //     $ids = $records->pluck('id')->implode(',');
    //     return redirect()->route('deposits.print', ['ids' => $ids]);
    // }),
                ]),
            ]);
    }

    /**
 * Calculates Buy Amount based on Sell Amount and Rate
 */
protected static function updateExchangeAmounts(callable $get, callable $set): void
{
    $sellAmount = (float) ($get('sell_amount') ?? 0);
    $rate = (float) ($get('rate') ?? 1);
    $operator = $get('divmul');

    if ($sellAmount <= 0 || $rate <= 0) return;

    $result = ($operator === 'Multiply') 
        ? ($sellAmount * $rate) 
        : ($sellAmount / $rate);

    $set('buy_amount', round($result, 2));
}

/**
 * (Optional) Calculates Sell Amount if the user manually types into the Buy Amount field
 */
protected static function updateReverseExchange(callable $get, callable $set): void
{
    $buyAmount = (float) ($get('buy_amount') ?? 0);
    $rate = (float) ($get('rate') ?? 1);
    $operator = $get('divmul');

    if ($buyAmount <= 0 || $rate <= 0) return;

    $result = ($operator === 'Multiply') 
        ? ($buyAmount / $rate) 
        : ($buyAmount * $rate);

    $set('sell_amount', round($result, 2));
}
}
