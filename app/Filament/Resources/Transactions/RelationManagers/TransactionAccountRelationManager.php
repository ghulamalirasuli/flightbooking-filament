<?php 
namespace App\Filament\Resources\Transactions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use App\Models\Account_ledger;
use App\Models\AddTransaction;
use App\Models\Accounts;
use App\Models\CashBox;
use App\Models\MoneyTransfer;
use App\Models\Currency;
use App\Models\Branch;

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

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


class TransactionAccountRelationManager extends RelationManager
{
    protected static string $relationship = 'accountFrom';
    protected static ?string $title = 'Account Ledgers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([]) 
            ->content(function ($livewire) {
                $ownerRecord = $livewire->getOwnerRecord();
                $referenceNo = $ownerRecord->reference_no;
                
                // Fetch ALL ledgers for this reference
                $allLedgers = Account_ledger::where('reference_no', $referenceNo)
                    ->with(['currencyInfo', 'accountInfo'])
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                if ($allLedgers->isEmpty()) {
                    return new \Illuminate\Support\HtmlString('<div style="padding: 20px; text-align: center; color: #6b7280; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">No ledger entries found for this batch</div>');
                }
                
                // Load all related accounts
                $accountUids = $allLedgers->pluck('account')->unique();
                $accounts = Accounts::whereIn('uid', $accountUids)
                    ->with('currency')
                    ->get()
                    ->keyBy('uid');
                
                // Group ledgers by Currency first, then by Account
                $ledgersByCurrency = $allLedgers->groupBy('currency');
                
                $currencyGroups = []; // THIS VARIABLE MUST BE DEFINED HERE
                
                foreach ($ledgersByCurrency as $currencyId => $currencyLedgers) {
                    $currencyInfo = $currencyLedgers->first()->currencyInfo;
                    
                    // Group by account within this currency
                    $accountGroups = [];
                    $accountLedgers = $currencyLedgers->groupBy('account');
                    
                    foreach ($accountLedgers as $uid => $ledgers) {
                        if (!isset($accounts[$uid])) continue;
                        
                        $accountGroups[] = [
                            'uid' => $uid,
                            'account' => $accounts[$uid],
                            'ledgers' => $ledgers,
                            'total_credit' => $ledgers->sum('credit'),
                            'total_debit' => $ledgers->sum('debit'),
                        ];
                    }
                    
                    if (!empty($accountGroups)) {
                        $currencyGroups[] = [
                            'currency_id' => $currencyId,
                            'currency' => $currencyInfo,
                            'accounts' => $accountGroups,
                        ];
                    }
                }
                
                // Pass $currencyGroups to the view
                return view('filament.relations.transaction-ledgers', [
                    'currencyGroups' => collect($currencyGroups),
                    'referenceNo' => $referenceNo
                ]);
            })
            ->paginated(false)
            ->striped(false)
            ->headerActions([
                // DEPOSIT FORM (Modal Popup)
                Action::make('deposit')
                    ->label('New Deposit')
                    ->icon('heroicon-m-arrow-down-circle')
                    ->color('success')
                    ->modalHeading('Create Deposit')
                    ->modalWidth('4xl')
                    ->form([
 // ROW 1: Branch and Date
                        Grid::make(12)->schema([

                                 Group::make([
                     
                                  Select::make('from_account')
                ->label('Related Account')
                ->options(function (RelationManager $livewire): array {
                    // Get the owner record (single transaction record)
                    $transaction = $livewire->getOwnerRecord();
                    
                    if (!$transaction || !$transaction->reference_no) {
                        return [];
                    }
                    
                    // ⚠️ KEY FIX: Fetch ALL transactions with the same reference_no
                    // This gets all accounts involved in the entire batch, not just the current record
                    $allTransactions = AddTransaction::where('reference_no', $transaction->reference_no)
                        ->with(['accountFrom', 'accountTo']) // Eager load
                        ->get();
                    
                    $accounts = [];
                    
                    foreach ($allTransactions as $trans) {
                        // Add account_from if exists
                        if ($trans->accountFrom) {
                            $account = $trans->accountFrom;
                            $label = $account->account_name_with_category_and_branch ?? $account->account_name;
                            if (!$account->is_active) {
                                $label .= ' (Inactive)';
                            }
                            // Use UID as key to avoid duplicates
                            $accounts[$account->uid] = $label;
                        }
                        
                        // Add account_to if exists
                        if ($trans->accountTo) {
                            $account = $trans->accountTo;
                            // Only add if different UID (prevents duplication)
                            if (!isset($accounts[$account->uid])) {
                                $label = $account->account_name_with_category_and_branch ?? $account->account_name;
                                if (!$account->is_active) {
                                    $label .= ' (Inactive)';
                                }
                                $accounts[$account->uid] = $label;
                            }
                        }
                    }
                    
                    return $accounts;
                })
                ->disableOptionWhen(fn (string $value): bool => 
                    !Accounts::where('uid', $value)->value('is_active')
                )
                ->searchable()
                ->required()
                ->columnSpanFull()
                ->helperText('All accounts involved in this transaction batch'),

                        ])->columnSpan(12),
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
                                    $accountUid = $get('account');
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

                        TextInput::make('amount')
                            ->label("Amount")
                            ->required()
                            ->numeric()
                            ->columnSpan(4),
                        ]),

                        // ROW 4: Description
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        DB::transaction(function () use ($data) {
                            $record = $this->getOwnerRecord();
                            
                            $cashBox = CashBox::create([
                                'uid'           => 'CBX' . now()->format('ymdhis') . rand(10,99),
                                'from_account'  => $data['from_account'],
                                'amount_from'   => $data['amount'],
                                'currency_from' => $data['currency_id'],
                                'currency_id'   => $data['currency_id'],
                                'reference_no'  => $record->reference_no,
                                'reference'     => 'CBR' . now()->format('ymdhis'),
                                'credit'        => $data['entry_type'] === 'Credit' ? $data['amount'] : 0,
                                'debit'         => $data['entry_type'] === 'Debit' ? $data['amount'] : 0,
                                'user_id'       => auth()->id(),
                                // 'branch_id' => auth()->user()->branch_id ?? 1,
                                'branch_id'     => $data['branch_id'],
                                'description'   => $data['description'],
                                'entry_type'    => $data['entry_type'],
                                'status'        => 'Pending',
                                'date_confirm' => now(),
                                'date_update'  => now(),
                            ]);
                             \Log::info('CashBox created: ' . $cashBox->id);
                               // Insert into account_ledger table
                        $ledger = Account_ledger::create([
                            'uid'           => 'CBX' . now()->format('ymdhis'). rand(10,99),
                            'account'       => $data['from_account'],
                            'reference_no'  =>  $record->reference_no,
                            'reference'     => 'CBR' . now()->format('ymdhis'),
                            'description'   => $data['description'],
                            'credit'        => $data['entry_type'] === 'Credit' ? $data['amount'] : 0,
                            'debit'         => $data['entry_type'] === 'Debit' ? $data['amount'] : 0,
                            'currency'      => $data['currency_id'],
                            'user_id'       => auth()->id(),
                            'branch_id'     => $data['branch_id'],
                            'date_confirm'  => now()->format('Y-m-d'),
                            'date_update'   => now()->format('Y-m-d'),
                            'pay_status'    =>'Cash'
                        ]);
                        \Log::info('Ledger created: ' . ($ledger->id ?? 'FAILED'));
                        });
                        
                        Notification::make()->title('Deposit created successfully')->success()->send();
                    }),

                // MONEY TRANSFER FORM
               Action::make('transfer')
                    ->label('Money Transfer')
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('warning')
                    ->modalHeading('Create Money Transfer')
                    ->modalWidth('4xl')
                    ->form([
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
                                       Select::make('from_account')
                ->label('Related Account')
                ->options(function (RelationManager $livewire): array {
                    // Get the owner record (single transaction record)
                    $transaction = $livewire->getOwnerRecord();
                    
                    if (!$transaction || !$transaction->reference_no) {
                        return [];
                    }
                    
                    // ⚠️ KEY FIX: Fetch ALL transactions with the same reference_no
                    // This gets all accounts involved in the entire batch, not just the current record
                    $allTransactions = AddTransaction::where('reference_no', $transaction->reference_no)
                        ->with(['accountFrom', 'accountTo']) // Eager load
                        ->get();
                    
                    $accounts = [];
                    
                    foreach ($allTransactions as $trans) {
                        // Add account_from if exists
                        if ($trans->accountFrom) {
                            $account = $trans->accountFrom;
                            $label = $account->account_name_with_category_and_branch ?? $account->account_name;
                            if (!$account->is_active) {
                                $label .= ' (Inactive)';
                            }
                            // Use UID as key to avoid duplicates
                            $accounts[$account->uid] = $label;
                        }
                        
                        // Add account_to if exists
                        if ($trans->accountTo) {
                            $account = $trans->accountTo;
                            // Only add if different UID (prevents duplication)
                            if (!isset($accounts[$account->uid])) {
                                $label = $account->account_name_with_category_and_branch ?? $account->account_name;
                                if (!$account->is_active) {
                                    $label .= ' (Inactive)';
                                }
                                $accounts[$account->uid] = $label;
                            }
                        }
                    }
                    
                    return $accounts;
                })
                ->disableOptionWhen(fn (string $value): bool => 
                    !Accounts::where('uid', $value)->value('is_active')
                )
                ->searchable()
                ->required()
                ->columnSpanFull()
                ->helperText('All accounts involved in this transaction batch'),


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
                    ])
                    ->action(function (array $data) {
                        $record = $this->getOwnerRecord();
                        

                        MoneyTransfer::create([
                                'uid' => 'M'.now()->format('ymdhis'),
                                'branch_id' => $data['branch_id'],
                                // 'branch_id' => auth()->user()->branch_id ?? 1,
                                'to_branch' => $data['to_branch'],
                                'user_id' => auth()->id(),
                                'reference_no' => $record->reference_no,
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
                                'reference_no' => $record->reference_no,
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
                                'reference_no' => $record->reference_no,
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
                        
                        
                        Notification::make()->title('Money Transfer created')->success()->send();
                    }),

           ]);
    }
}