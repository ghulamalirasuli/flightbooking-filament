<?php

namespace App\Filament\Resources\MoneyTransfers\Tables;
use Illuminate\Support\Facades\Auth;

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
use Filament\Schemas\Components\Group;

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
use App\Models\User;
use App\Models\MoneyTransfer;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

class MoneyTransfersTable
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

                
          TextColumn::make('account_from')
                ->label('From Account')
                ->formatStateUsing(function ($record) {
                    // Change $record->account to $record->accountFrom
                    return $record->accountFrom?->account_name_with_category_and_branch ?? 'N/A';
                })
                ->searchable(),

            TextColumn::make('account_to')
                ->label('To Account')
                ->formatStateUsing(function ($record) {
                    // Change $record->account to $record->accountTo
                    return $record->accountTo?->account_name_with_category_and_branch ?? 'N/A';
                })
                ->searchable(),

                
            TextColumn::make('amount') // Unique identifier
                    ->label('Amount')
                    ->state(fn ($record): string => $record->mtcurrency?->currency_code ?? '') 
                    ->description(fn ($record): string => $record->amount ?? '0'),

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

                TextColumn::make('description')
                    ->label('Description')
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ])
            ->headerActions([// ///////////////// HEADER ACTIONS-------------
                Action::make('print_pdf')
    ->label('Download PDF')
    ->icon('heroicon-o-arrow-down-tray')
    ->color('success')
    ->url(function ($livewire) {
        $filters = $livewire->tableFilters;
        return route('transfer.print_all', [
            'filters' => $filters,
            'format' => 'pdf' // Add this flag
        ]);
    })
    ->openUrlInNewTab(),
    
Action::make('print_all')
    ->label('Print All Transfers')
    ->icon('heroicon-o-printer')
    ->color('info')
    ->url(function ($livewire) { // Add $livewire here
        $currentUserId = auth()->id();
        $currentUser = \App\Models\User::find($currentUserId);
        
        // Get active table filters
        $filters = $livewire->tableFilters; 

        $params = ['filters' => $filters];

        if (!$currentUser->is_admin) {
            $params['branch_id'] = $currentUser->branch_id;
        }

        return route('transfer.print_all', $params);
    })
    ->openUrlInNewTab(),

                Action::make('transfer')
                    ->label('New Transafer')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('info')
                    ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
            // Start Form----------------------FORM--------------------------------
                    ->form([
                        Section::make()
                            ->schema([
                                // --- ROW 1: BRANCH SELECTION ---
                                Grid::make(12)->schema([
                                    // 1. FROM BRANCH
                                    Select::make('branch_id')
                                        ->label('From Branch')
                                        ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                                        ->live()
                                        ->afterStateUpdated(function ($set) {
                                            $set('account_from', null);
                                            $set('to_branch', null); // Reset To Branch if From changes
                                            $set('account_to', null);
                                        })
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(6),

                                    // 2. TO BRANCH (Excludes the selected From Branch)
                                    Select::make('to_branch')
                                        ->label('To Branch')
                                        ->options(function (callable $get) {
                                            // $fromBranch = $get('branch_id');
                                            return Branch::where('status', true)
                                                // ->when($fromBranch, fn ($q) => $q->where('id', '!=', $fromBranch)) // Exclude Kabul if selected in From
                                                ->pluck('branch_name', 'id');
                                        })
                                        ->live()
                                        ->afterStateUpdated(fn ($set) => $set('account_to', null))
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(6),
                                ])->columnSpanFull(),

                                Grid::make(12)->schema([
                                    // --- FROM ACCOUNT COLUMN ---
                                    Group::make([
                                        Select::make('account_from')
                                            ->label('From Account')
                                            ->live()
                                            ->options(function (callable $get) {
                                                $branchId = $get('branch_id');
                                                if (! $branchId) {
                                                    return [];
                                                }

                                                return \App\Models\Accounts::query()
                                                    ->with(['accountType', 'branch'])
                                                    ->where('is_active', true)
                                                    ->where('branch_id', $branchId)
                                                    ->get()
                                                    ->mapWithKeys(function ($account) {
                                                        return [$account->uid => "({$account->branch?->branch_name}) {$account->account_name} - {$account->accountType?->accounts_category}"];
                                                    });
                                            })
                                            ->searchable()
                                            ->required(),

                                        Placeholder::make('from_balance_preview')
                                            ->hiddenLabel()
                                            ->visible(fn ($get) => filled($get('account_from'))) // Fixes the empty space issue
                                            ->content(fn ($get) => view('filament.components.account-balance-table', [
                                                'accountUid' => $get('account_from'),
                                            ])),
                                    ])->columnSpan(6),

                                    // --- TO ACCOUNT COLUMN ---
                                    Group::make([
                                        Select::make('account_to')
                                            ->label('To Account')
                                            ->live() // Added live() so the preview shows up instantly
                                            ->options(function (callable $get) {
                                                $toBranchId = $get('to_branch');
                                                $fromAccountUid = $get('account_from');
                                                if (! $toBranchId) {
                                                    return [];
                                                }

                                                return \App\Models\Accounts::query()
                                                    ->with(['accountType', 'branch'])
                                                    ->where('is_active', true)
                                                    ->where('branch_id', $toBranchId)
                                                    ->when($fromAccountUid, fn ($q) => $q->where('uid', '!=', $fromAccountUid))
                                                    ->get()
                                                    ->mapWithKeys(function ($account) {
                                                        return [$account->uid => "({$account->branch?->branch_name}) {$account->account_name} - {$account->accountType?->accounts_category}"];
                                                    });
                                            })
                                            ->searchable()
                                            ->required(),

                                        Placeholder::make('to_balance_preview')
                                            ->hiddenLabel()
                                            ->visible(fn ($get) => filled($get('account_to'))) // Fixes the empty space issue
                                            ->content(fn ($get) => view('filament.components.account-balance-table', [
                                                'accountUid' => $get('account_to'),
                                            ])),
                                    ])->columnSpan(6),
                                ])->columnSpanFull(),

                                Grid::make(12)->schema([
                                    TextInput::make('amount')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(3),

                                    Select::make('currency')
                                        ->label('Currency')
                                        ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                                        ->required()
                                        ->columnSpan(3),

                                    Textarea::make('description')
                                        ->columnSpan(6)
                                        ->rows(2),
                                ])->columnSpanFull(),
                            ]),
                    ])
            // 1. Hide the default primary button
                    ->modalSubmitAction(false)

    // 2. Define both buttons in the footer to control order
                    ->extraModalFooterActions(fn (Action $action): array => [
                        // This button will now be in the "corner" (leftmost)
                        $action->makeModalSubmitAction('saveAndNew', arguments: ['another' => true])
                            ->label('Save & New')
                            ->color('gray'),

                        // This button will be in the "middle" (replaces the old Submit)
                        $action->makeModalSubmitAction('save')
                            ->label('Save')
                            ->color('warning'), // 'warning' matches the yellow color in your screenshot
                    ])
                    ->action(function (array $data, Action $action, $form, array $arguments): void {
                        DB::transaction(function () use ($data) {
                            MoneyTransfer::create([
                                'uid' => 'M'.now()->format('ymdhis'),
                                'branch_id' => $data['branch_id'],
                                'to_branch' => $data['to_branch'],
                                'user_id' => auth()->id(),
                                'reference_no' => 'MTR'.now()->format('ymdhis'),
                                'reference' => 'MT'.now()->format('ymdhis'),
                                'account_from' => $data['account_from'],
                                'account_to' => $data['account_to'],
                                'amount' => $data['amount'],
                                'currency' => $data['currency'],

                                'description' => $data['description'],
                                'commission' => 0,
                                'date_confirm' => now()->format('Y-m-d'),
                                'date_update' => now()->format('Y-m-d'),
                            ]);
                            // ------------ From----------
                            Account_ledger::create([
                                'uid' => 'M'.now()->format('ymdhis'),
                                'account' => $data['account_from'],
                                'reference_no' => 'MTR'.now()->format('ymdhis'),
                                'reference' => 'MT'.now()->format('ymdhis'),
                                'description' => $data['description'],
                                'credit' => $data['amount'],
                                'debit' => 0,
                                'currency' => $data['currency'],
                                'user_id' => auth()->id(),
                                'branch_id' => $data['branch_id'],
                                'date_confirm' => now()->format('Y-m-d'),
                                'date_update' => now()->format('Y-m-d'),
                                'pay_status' => 'Transfer',
                            ]);

                            // --------To---------
                            Account_ledger::create([
                                'uid' => 'M'.now()->format('ymdhis'),
                                'account' => $data['account_to'],
                                'reference_no' => 'MTR'.now()->format('ymdhis'),
                                'reference' => 'MT'.now()->format('ymdhis'),
                                'description' => $data['description'],
                                'credit' => 0,
                                'debit' => $data['amount'],
                                'currency' => $data['currency'],
                                'user_id' => auth()->id(),
                                'branch_id' => $data['to_branch'],
                                'date_confirm' => now()->format('Y-m-d'),
                                'date_update' => now()->format('Y-m-d'),
                                'pay_status' => 'Transfer',
                            ]);
                        });
                        Notification::make()->title('Transfer Saved')->success()->send();
                        // Now these variables will work
                        if ($arguments['another'] ?? false) {
                            // Tip: We keep the branch_id so the user doesn't have to re-select it
                            $form->fill(['branch_id' => $data['branch_id']]);
                            $action->halt();
                        }
                    }),
                // End Form
            ])

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
               
                SelectFilter::make('currency')
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

                        SelectFilter::make('user_id')
                    ->label('User')
                    ->options(function () {
                        return User::query()
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->columnSpan(2),

                  
            ], FiltersLayout::Modal)
            ->deferFilters(false)
            ->filtersFormColumns(12)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->filtersFormWidth(Width::Full)
              ->recordActions([
               ActionGroup::make([

                // --- CONFIRM ACTION ---
        Action::make('confirm')
            ->label('Confirm')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn ($record) => $record->status !== 'Confirmed')
            ->action(function ($record) {
                // 1. Update the CashBox record
                // $record->update(['status' => 'Confirmed','update_by' => auth()->id()]); // single row by ID
                // 1. Update ALL rows in CashBox with this reference number
            MoneyTransfer::where('reference_no', $record->reference_no)
                ->update([
                    'status' => 'Confirmed',
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);

                // 2. Update the related Account Ledger record
                Account_ledger::where('reference_no', $record->reference_no)
                    ->update(['status' => 'Confirmed']);

                Notification::make()->title('Transfer Confirmed')->success()->send();
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
                // $record->update(['status' => 'Cancelled','update_by' => auth()->id()]);

                   MoneyTransfer::where('reference_no', $record->reference_no)
                ->update([
                    'status' => 'Cancelled',
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);
                // 2. Update the related Account Ledger record
                Account_ledger::where('reference_no', $record->reference_no)
                    ->update(['status' => 'Cancelled']);

                Notification::make()->title('Transfer Cancelled')->danger()->send();
            }),

        // --- SET PENDING ACTION ---
        Action::make('setPending')
            ->label('Mark as Pending')
            ->icon('heroicon-m-pause-circle')
            ->color('gray')
            ->visible(fn ($record) => $record->status !== 'Pending')
            ->action(function ($record) {
                   MoneyTransfer::where('reference_no', $record->reference_no)
                ->update([
                    'status' => 'Pending',
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);

                // 2. Update the related Account Ledger record
                Account_ledger::where('reference_no', $record->reference_no)
                    ->update(['status' => 'Pending']);

                Notification::make()->title('Transfer set to Pending')->info()->send();
            }),


             Action::make('print')
            ->label('Print')
            ->icon('heroicon-m-printer')
            ->color('info')
            // Pass the single ID as 'record'
            ->url(fn ($record) => route('transfer.print', ['record' => $record->id]))
            ->openUrlInNewTab(),


                ViewAction::make(),
                // EditAction::make(),
                 EditAction::make('edit_transfer')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit Transfer')
                    ->form([
                        // ------------EDIT Form Fields goes here-----
                          Section::make()
                            ->schema([
                                // --- ROW 1: BRANCH SELECTION ---
                                Grid::make(12)->schema([
                                    // 1. FROM BRANCH
                                    Select::make('branch_id')
                                        ->label('From Branch')
                                        ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                                        ->live()
                                        ->afterStateUpdated(function ($set) {
                                            $set('account_from', null);
                                            $set('to_branch', null); // Reset To Branch if From changes
                                            $set('account_to', null);
                                        })
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(6),

                                    // 2. TO BRANCH (Excludes the selected From Branch)
                                    Select::make('to_branch')
                                        ->label('To Branch')
                                        ->options(function (callable $get) {
                                            // $fromBranch = $get('branch_id');
                                            return Branch::where('status', true)
                                                // ->when($fromBranch, fn ($q) => $q->where('id', '!=', $fromBranch)) // Exclude Kabul if selected in From
                                                ->pluck('branch_name', 'id');
                                        })
                                        ->live()
                                        ->afterStateUpdated(fn ($set) => $set('account_to', null))
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(6),
                                ])->columnSpanFull(),

                                Grid::make(12)->schema([
                                    // --- FROM ACCOUNT COLUMN ---
                                    Group::make([
                                        Select::make('account_from')
                                            ->label('From Account')
                                            ->live()
                                            ->options(function (callable $get) {
                                                $branchId = $get('branch_id');
                                                if (! $branchId) {
                                                    return [];
                                                }

                                                return \App\Models\Accounts::query()
                                                    ->with(['accountType', 'branch'])
                                                    ->where('is_active', true)
                                                    ->where('branch_id', $branchId)
                                                    ->get()
                                                    ->mapWithKeys(function ($account) {
                                                        return [$account->uid => "({$account->branch?->branch_name}) {$account->account_name} - {$account->accountType?->accounts_category}"];
                                                    });
                                            })
                                            ->searchable()
                                            ->required(),

                                        Placeholder::make('from_balance_preview')
                                            ->hiddenLabel()
                                            ->visible(fn ($get) => filled($get('account_from'))) // Fixes the empty space issue
                                            ->content(fn ($get) => view('filament.components.account-balance-table', [
                                                'accountUid' => $get('account_from'),
                                            ])),
                                    ])->columnSpan(6),

                                    // --- TO ACCOUNT COLUMN ---
                                    Group::make([
                                        Select::make('account_to')
                                            ->label('To Account')
                                            ->live() // Added live() so the preview shows up instantly
                                            ->options(function (callable $get) {
                                                $toBranchId = $get('to_branch');
                                                $fromAccountUid = $get('account_from');
                                                if (! $toBranchId) {
                                                    return [];
                                                }

                                                return \App\Models\Accounts::query()
                                                    ->with(['accountType', 'branch'])
                                                    ->where('is_active', true)
                                                    ->where('branch_id', $toBranchId)
                                                    ->when($fromAccountUid, fn ($q) => $q->where('uid', '!=', $fromAccountUid))
                                                    ->get()
                                                    ->mapWithKeys(function ($account) {
                                                        return [$account->uid => "({$account->branch?->branch_name}) {$account->account_name} - {$account->accountType?->accounts_category}"];
                                                    });
                                            })
                                            ->searchable()
                                            ->required(),

                                        Placeholder::make('to_balance_preview')
                                            ->hiddenLabel()
                                            ->visible(fn ($get) => filled($get('account_to'))) // Fixes the empty space issue
                                            ->content(fn ($get) => view('filament.components.account-balance-table', [
                                                'accountUid' => $get('account_to'),
                                            ])),
                                    ])->columnSpan(6),
                                ])->columnSpanFull(),

                                Grid::make(12)->schema([
                                    TextInput::make('amount')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(3),

                                    Select::make('currency')
                                        ->label('Currency')
                                        ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                                        ->required()
                                        ->columnSpan(3),

                                    Textarea::make('description')
                                        ->columnSpan(6)
                                        ->rows(2),
                                ])->columnSpanFull(),
                            ]),
                    ])

                        ->action(function ($record, array $data): void {
                          \DB::transaction(function () use ($record, $data) {
                            // 1. Update the Deposit Record (CashBox)
                            $record->update([
                                'branch_id' => $data['branch_id'],
                                'to_branch' => $data['to_branch'],
                                'user_id' => auth()->id(),
                                'account_from' => $data['account_from'],
                                'account_to' => $data['account_to'],
                                'amount' => $data['amount'],
                                'currency' => $data['currency'],
                                'description' => $data['description'],
                                'commission' => 0,
                                'date_update' => now()->format('Y-m-d'),
                                'update_by'=> auth()->id(),
                            ]);
                            // ------------ From----------
                            Account_ledger::where('reference_no', $record->reference_no)->where('reference', $record->reference)->where('account', $record->account_from)->update([
                                'account' => $data['account_from'],
                                'description' => $data['description'],
                                'credit' => $data['amount'],
                                'debit' => 0,
                                'currency' => $data['currency'],
                                'user_id' => auth()->id(),
                                'branch_id' => $data['branch_id'],
                                'date_update' => now()->format('Y-m-d'),
                            ]);

                            // --------To---------
                             Account_ledger::where('reference_no', $record->reference_no)->where('reference', $record->reference)->where('account', $record->account_to)->update([
                                'account' => $data['account_to'],
                                'description' => $data['description'],
                                'credit' => 0,
                                'debit' => $data['amount'],
                                'currency' => $data['currency'],
                                'user_id' => auth()->id(),
                                'branch_id' => $data['to_branch'],
                                'date_update' => now()->format('Y-m-d'),
                            ]);
        });

        \Filament\Notifications\Notification::make()
            ->title('Transfer updated successfully')
            ->success()
            ->send();
    }),

                // SYNCED DELETE
        DeleteAction::make()
            ->action(function ($record) {
                DB::transaction(function () use ($record) {
                    // Soft delete the ledger entry
                    Account_ledger::where('reference_no', $record->reference_no)->delete();
                    // Soft delete the main record
                    $record->delete();
                });
                Notification::make()->title('Transfer and Ledger moved to trash')->success()->send();
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
                Notification::make()->title('Transfer and Ledger restored')->success()->send();
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
                Notification::make()->title('Transfer and Ledger deleted permanently')->danger()->send();
            }),

            ])
              ])
               ->defaultSort('created_at', 'desc') // Change 'desc' to 'asc' if you want oldest first
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

                ]),
            ]);
    }
}
