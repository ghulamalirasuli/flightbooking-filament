<?php

namespace App\Filament\Resources\Transactions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\Transactions\Schemas\TransactionEditForm; // Import the class
// use Filament\Forms\Form; // Important: Import the Form class

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Filters\TrashedFilter;


use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Group;



use App\Models\Account_ledger;
use App\Models\Income_expense;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\CashBox;
use App\Models\Service;
use App\Models\DocType;
use App\Models\AddTransaction;

use Illuminate\Database\Eloquent\Model; // Add this line
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Import this at the top

use Filament\Notifications\Notification;

class BatchRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'batchRecords';

    protected static ?string $title = 'Related Items (Same Reference)';

     public function isReadOnly(): bool // Disable read-only mode (to see action buttons)
    {
        return false;
    }

    protected function getTableHeading(): string
{
    return 'Transactions (' . $this->getBatchCurrency() . ')';
}

protected function getBatchCurrency(): string
{
    return optional(
        $this->getOwnerRecord()
            ->profitCurrency
    )->currency_code ?? '-';
}

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fullname')
            ->columns([
              TextColumn::make('user.name')
                ->label('Inserted')
                ->formatStateUsing(function ($record) {
                    $user = $record->user?->name ?? 'System';
                    $date = $record->created_at?->format('M d, Y H:i') ?? '-'; // Jan 25, 2026 14:30
                    
                    return "{$user}<br><span style='color: #6b7280; font-size: 0.75rem;'>{$date}</span>";
                })
                ->html()
                ->searchable(['user.name']),
                
            // TextColumn::make('fullname')
            //     ->label('Details')
            //     ->formatStateUsing(fn ($record) => "{$record->fullname} |{$record->doc_number} | {$record->description}")
            //     ->searchable(['fullname','doc_number', 'description']),
                    TextColumn::make('fullname')
    ->label('Details')
    ->formatStateUsing(function ($record) {
        $fullname = $record->fullname ?? '-';
         $doc_number = $record->doc_number ?? '-';
          $description = $record->description ?? '-';
        
        return "{$fullname} | Doc No. {$doc_number}<br><span style='color: #6b7280; font-size: 0.875rem;'>{$description}</span>";
    })
    ->html()
    ->searchable()
    ->sortable(),
                TextColumn::make('service.title')
                ->label('Sevice')
                ->default('-'),

              TextColumn::make('accountFrom.account_name_with_category_and_branch')
    ->label('From Account')
    ->formatStateUsing(function ($record) {
        $account = $record->accountFrom?->account_name_with_category_and_branch ?? '-';
        $price = number_format($record->fixed_price ?? 0, 2);
        $currency = $record->currencyFrom?->currency_code ?? '';
        
        return "{$account}<br><span style='color: #6b7280; font-size: 0.875rem;'>{$price} {$currency}</span>";
    })
    ->html()
    ->searchable()
    ->sortable(),

TextColumn::make('accountTo.account_name_with_category_and_branch')
    ->label('To Account')
    ->formatStateUsing(function ($record) {
        $account = $record->accountTo?->account_name_with_category_and_branch ?? '-';
        $price = number_format($record->sold_price ?? 0, 2);
        $currency = $record->currencyTo?->currency_code ?? '';
        
        return "{$account}<br><span style='color: #6b7280; font-size: 0.875rem;'>{$price} {$currency}</span>";
    })
    ->html()
    ->searchable()
    ->sortable(),

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
        Sum::make()
            ->label('Total Profit')
            ->formatStateUsing(fn ($state) => number_format($state, 2))
            ->suffix(' ' . $this->getBatchCurrency()) // ✅ NOT hard-coded
    ),

 


                // TextColumn::make('doc_number')
                //     ->label('Doc #'),
            
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
            ])
            ->filters([
                // You can copy filters from TransactionsTable here if needed
                TrashedFilter::make(),
            ])
         ->headerActions([
              CreateAction::make()
                    ->label('Add More Items')
                    ->modalHeading('Add Multiple Documents to Batch')
                    ->modalWidth('7xl')
                    ->form([
                        // 1. SHARED SECTION (Fields common to all entries in this batch)
                        Section::make('Shared Info')
    ->description('These details apply to all documents added below')
    ->schema([
        Grid::make(12)->schema([
            // 1. FROM BRANCH
            Select::make('branch_id')
                ->relationship('branch', 'branch_name')
                ->required()
                ->live()
                ->columnSpan(6),

            // 2. TO BRANCH
            Select::make('to_branch')
                ->relationship('branch', 'branch_name')
                ->required()
                ->live()
                ->columnSpan(6),

            Select::make('account_from')
            ->label('From Account')
            ->options(function (callable $get) {
                $branchId = $get('branch_id');
                if (!$branchId) {
                    return [];
                }
                
                return \App\Models\Accounts::query()
                    ->with(['accountType', 'branch'])
                    ->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->get()
                    ->mapWithKeys(function ($account) {
                        return [
                            $account->uid => $account->account_name_with_category_and_branch
                        ];
                    });
            })
            ->searchable()
            ->required()
            ->columnSpan(6),

            
            Select::make('account_to')
                ->label('To Account')
                ->options(function (callable $get) {
                    $toBranchId = $get('to_branch');
                    if (!$toBranchId) {
                        return [];
                    }
                    
                    return \App\Models\Accounts::query()
                        ->with(['accountType', 'branch'])
                        ->where('branch_id', $toBranchId)
                        ->where('is_active', true)
                        ->get()
                        ->mapWithKeys(function ($account) {
                            return [
                                $account->uid => $account->account_name_with_category_and_branch
                            ];
                        });
                })
                ->searchable()
                ->required()
                ->columnSpan(6),

            // 5. SERVICE TYPE (FIXED LOGIC)
            // Use 'options' instead of 'relationship' query modification if the filtering 
            // relies on a JSON array (active_services) on the Branch model.
            Select::make('service_type')
                ->label('Service')
                ->options(function ($get) {
                    $branchId = $get('branch_id');
                    if (! $branchId) {
                        return [];
                    }
                    
                    // Find the branch to get its active_services list
                    $branch = Branch::find($branchId);

                    if (! $branch || empty($branch->active_services)) {
                        return [];
                    }

                    // Filter services where ID is in the branch's allowed list
                    return Service::whereIn('id', $branch->active_services)
                        ->pluck('title', 'id');
                })
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(fn ($state, $set) => $set('service_content', Service::find($state)?->content ?? ''))
                ->columnSpan(6),

            DatePicker::make('delivery_date')->native(false)->columnSpan(2),
            DatePicker::make('depart_date')->native(false)->columnSpan(2),
            DatePicker::make('arrival_date')->native(false)->columnSpan(2),
        ]),
    ]),

                        // 2. REPEATER SECTION (Document specific info)
                        Section::make('Documents')
                            ->schema([
                                Repeater::make('documents')
                                    ->label('Document List')
                                    ->schema([
                                        Grid::make(12)->schema([
                                            TextInput::make('fullname')->required()->columnSpan(3),
                                            Textarea::make('description')->rows(1)->columnSpan(3),
                                            TextInput::make('fixed_price')
                                                ->numeric()
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateProfitForItem($get, $set))
                                                ->columnSpan(3),

                                            TextInput::make('sold_price')
                                                ->numeric()
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateProfitForItem($get, $set))
                                                ->columnSpan(3),
                                            
                                            TextInput::make('doc_number')->columnSpan(3),
                                            Select::make('doc_type')
                                                ->options(DocType::where('status', true)->pluck('doctype', 'id'))
                                                ->required()->columnSpan(3),
                                           Select::make('from_currency')
                                                ->relationship('currencyFrom', 'currency_name')
                                                ->required()
                                                ->live() // Add this
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateProfitForItem($get, $set)) // Add this
                                                ->columnSpan(3),

                                            Select::make('to_currency')
                                                ->relationship('currencyTo', 'currency_name')
                                                ->required()
                                                ->live() // Add this
                                                ->afterStateUpdated(fn ($get, $set) => self::calculateProfitForItem($get, $set)) // Add this
                                                ->columnSpan(3),
                                            
                                            // Hidden profit field to be filled by calculation
                                            // Initialize with a default value
                                                TextInput::make('profit')
                                                    ->default(0) // Add this
                                                    ->hidden()
                                                    ->dehydrated(true),
                                        ]),
                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel('Add Another Document')
                                    ->collapsible()
                            ]),
                    ])
                    ->using(function (array $data, string $model): Model {
        return DB::transaction(function () use ($data, $model) {
            $parentRecord = $this->getOwnerRecord();
            $lastRecord = null;
            
            // Get the default currency ID once to use inside the loop
            $defaultCurrencyId = Currency::where('defaults', true)->value('id');

            foreach ($data['documents'] as $doc) {
                // --- SERVER-SIDE CALCULATION ---
                // We calculate profit here manually to avoid "Undefined array key" errors
                $fixed = (float) ($doc['fixed_price'] ?? 0);
                $sold  = (float) ($doc['sold_price'] ?? 0);
                $fromId = $doc['from_currency'] ?? null;
                $toId   = $doc['to_currency'] ?? null;


                $calculatedProfit = 0;
                if ($fixed > 0 && $sold > 0 && $fromId && $toId) {
                    $fcur = Currency::find($fromId)?->buy_rate ?? 1;
                    $tcur = Currency::find($toId)?->sell_rate ?? 1;
                    $calculatedProfit = ($sold / $tcur) - ($fixed / $fcur);
                }

            $serviceContent = Service::where('id', $data['service_type'] ?? null)->value('content');

                $recordData = [
                    'reference_no'    => $parentRecord->reference_no,
                    'uid'             => 'TRX' . now()->format('ymdHis') . rand(10, 99),
                    'reference'       => 'R-' . now()->format('ymdHis') . rand(1000, 9999),
                    'user_id'         => auth()->id(),
                    'status'          => 'Pending',
                    'pay_status'      => 'Unpaid',
                    'date_confirm'    => now(),
                    'date_update'    => now(),
                    
                    // Shared Fields from the main form data
                    'branch_id'       => $data['branch_id'],
                    'to_branch'       => $data['to_branch'],
                    'account_from'    => $data['account_from'],
                    'account_to'      => $data['account_to'],
                    'service_type'    => $data['service_type'],
                    'service_content' => $serviceContent,
                    'delivery_date'   => $data['delivery_date'],
                    'depart_date'     => $data['depart_date'],
                    'arrival_date'    => $data['arrival_date'],
                    'default_currency'=> $defaultCurrencyId,

                    // Repeater Fields from the $doc array
                    'fullname'        => $doc['fullname'],
                    'description'     => $doc['description'],
                    'fixed_price'     => $fixed,
                    'sold_price'      => $sold,
                    'doc_number'      => $doc['doc_number'],
                    'doc_type'        => $doc['doc_type'],
                    'from_currency'   => $fromId,
                    'to_currency'     => $toId,
                    'profit'          => round($calculatedProfit, 2), // Use our calculated value
                ];

                $record = $model::create($recordData);
                $lastRecord = $record;

                // --- Create Ledgers ---
                Account_ledger::create([
                    'uid' => $record->uid, 
                    'account' => $record->account_from,
                    'reference_no' => $record->reference_no, 
                    'reference' => $record->reference,
                    'debit' => 0,
                    'credit' => $record->fixed_price, 
                    'currency' => $record->from_currency,
                    'branch_id' => $record->branch_id, 
                    'description' => $record->description,
                    'date_confirm' => $record->date_confirm,
                    'date_update' => $record->date_update,
                    'service_id'    => $record->service_type,
                ]);

                Account_ledger::create([
                    'uid' => $record->uid, 
                    'account' => $record->account_to,
                    'reference_no' => $record->reference_no, 
                    'reference' => $record->reference,
                    'debit' => $record->sold_price, 
                    'credit' => 0,
                    'currency' => $record->to_currency,
                    'branch_id' => $record->branch_id,
                     'description' => $record->description, 
                    'date_confirm' => $record->date_confirm,
                    'date_update' => $record->date_update,
                    'service_id'    => $record->service_type,
                ]);

                Income_expense::create([
                    'uid' => $record->uid, 
                    'user_id' => auth()->id(), 
                    'branch_id' => $record->branch_id,
                    'type' => 'Income', 
                    'debit' => 0,
                    'credit' => $record->profit, 
                    'currency' => $record->default_currency,
                     'description' => $record->description,
                    'reference_no' => $record->reference_no, 
                    'reference' => $record->reference,
                    'date_confirm' => $record->date_confirm,
                    'date_update' => $record->date_update,
                    'service_uid'    => $record->service_type,
                ]);
            }

            return $lastRecord;
        });
    }),
            ])
            ->actions([
                ActionGroup::make([
                   EditAction::make()
    ->form(fn (Schema $schema) => TransactionEditForm::configure($schema))
    ->modalWidth('7xl')
    ->using(function (Model $record, array $data): Model {
        return DB::transaction(function () use ($record, $data) {
            // 1. Recalculate Profit
            $fcur = \App\Models\Currency::where('id', $data['from_currency'])->value('buy_rate') ?? 1;
            $tcur = \App\Models\Currency::where('id', $data['to_currency'])->value('sell_rate') ?? 1;
            $profit = ($data['sold_price'] / $tcur) - ($data['fixed_price'] / $fcur);
            $data['profit'] = round($profit, 2);
            $data['date_update'] = now();

            // 2. Update Transaction
            $record->update($data);

            // 3. Update FROM Ledger
            Account_ledger::where('uid', $record->uid)
                ->where('credit', '>', 0)
                ->update([
                    'account' => $data['account_from'],
                    'debit' => 0,
                    'credit' => $data['fixed_price'],
                    'currency' => $data['from_currency'],
                    'description' => $data['description'],
                    'service_id'   =>  $data['service_type'], 
                ]);

            // 4. Update TO Ledger
            Account_ledger::where('uid', $record->uid)
                ->where('debit', '>', 0)
                ->update([
                    'account' => $data['account_to'],
                    'debit' => $data['sold_price'],
                    'credit' => 0,
                    'currency' => $data['to_currency'],
                    'description' => $data['description'],
                    'service_id'   =>  $data['service_type'], 
                ]);

            // 5. Update Income Ledger
            Income_expense::where('uid', $record->uid)
                ->update([
                    'credit' => $data['profit'],
                    'debit' => 0,
                    'service_uid'   =>  $data['service_type'], 
                    'description' => 'Profit from: ' . $data['fullname'],
                ]);

            return $record;
        });
    }),

                     DeleteAction::make()
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            // Soft delete the ledger entry
                            Account_ledger::where('reference', $record->reference)->delete();
                            Income_expense::where('reference', $record->reference)->delete();
                            // Soft delete the main record
                            $record->delete();
                        });
                        Notification::make()->title('Transaction record moved to trash')->success()->send();
                    }),
                     // SYNCED RESTORE
                    RestoreAction::make()
                        ->action(function ($record) {
                            DB::transaction(function () use ($record) {
                                // Restore the ledger entry (using withTrashed to find it)
                                Account_ledger::withTrashed()->where('reference', $record->reference)->restore();
                                Income_expense::withTrashed()->where('reference', $record->reference)->restore();
                                // Restore the main record
                                $record->restore();
                            });
                            Notification::make()->title('Transaction record restored')->success()->send();
                        }),
                     // SYNCED FORCE DELETE
                    ForceDeleteAction::make()
                        ->label('Delete forever')
                        ->action(function ($record) {
                            DB::transaction(function () use ($record) {
                                // Permanently delete the ledger entry
                                Account_ledger::withTrashed()->where('reference', $record->reference)->forceDelete();
                                Income_expense::withTrashed()->where('reference', $record->reference)->forceDelete();
                                // Permanently delete the main record
                                $record->forceDelete();
                            });
                        Notification::make()->title('Transaction record deleted permanently')->danger()->send();
                    }),


                    // --- CONFIRM ACTION ---
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== 'Confirmed')
                    ->action(function ($record) {
                        AddTransaction::where('reference', $record->reference)
                        ->update([
                            'status' => 'Confirmed',
                            'update_by' => auth()->id(),
                            'date_update' => now()->format('Y-m-d'),
                        ]);

                        // 2. Update the related Account Ledger record
                        Account_ledger::where('reference', $record->reference)->update(['status' => 'Confirmed']);
                        Income_expense::where('reference', $record->reference)->update(['status' => 'Confirmed']);

                        Notification::make()->title('Transaction Confirmed')->success()->send();
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

                            AddTransaction::where('reference', $record->reference)
                            ->update([
                                'status' => 'Cancelled',
                                'update_by' => auth()->id(),
                                'date_update' => now()->format('Y-m-d'),
                            ]);
                            // 2. Update the related Account Ledger record
                            Account_ledger::where('reference', $record->reference)->update(['status' => 'Cancelled']);
                            Income_expense::where('reference', $record->reference)->update(['status' => 'Cancelled']);

                            Notification::make()->title('Transaction Cancelled')->danger()->send();
                        }),

                             // --- SET PENDING ACTION ---
        Action::make('setPending')
            ->label('Mark as Pending')
            ->icon('heroicon-m-pause-circle')
            ->color('gray')
            ->visible(fn ($record) => $record->status !== 'Pending')
            ->action(function ($record) {
                // 1. Update the CashBox record
                // $record->update(['status' => 'Pending','update_by' => auth()->id()]);
                AddTransaction::where('reference', $record->reference)->update([
                    'status' => 'Pending',
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);

                // 2. Update the related Account Ledger record
                Account_ledger::where('reference', $record->reference)->update(['status' => 'Pending']);
                Income_expense::where('reference', $record->reference)->update(['status' => 'Pending']);

                Notification::make()->title('Transaction set to Pending')->info()->send();
            }),

                                     // --- SET SPLIT ACTION ---
        Action::make('setSplit')
            ->label('Mark as Split')
            ->icon('heroicon-m-pause-circle')
            ->color('warning')
            ->visible(fn ($record) => $record->status !== 'Confirmed')
            ->action(function ($record) {
               $sreference_no = 'STRF' . now()->format('ymdHis') . strtoupper(Str::random(9));
                // $record->update(['status' => 'Pending','update_by' => auth()->id()]);
                AddTransaction::where('reference', $record->reference)->update([
                    'reference_no' => $sreference_no,
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);

                // 2. Update the related Account Ledger record
                Account_ledger::where('reference', $record->reference)->update(['reference_no' => $sreference_no,]);
                Income_expense::where('reference', $record->reference)->update(['reference_no' => $sreference_no,]);

                Notification::make()->title('Transaction Splitted')->info()->send();
            }),

                ]),
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
                    $refs = $records->pluck('reference')->toArray();

                    // Update CashBox records
                    $records->each->update(['status' => 'Pending','update_by' => auth()->id()]);

                    // Update corresponding Ledgers
                    Account_ledger::whereIn('reference', $refs)->update(['status' => 'Pending']);
                    Income_expense::whereIn('reference', $refs)->update(['status' => 'Pending']);
                });
                
                \Filament\Notifications\Notification::make()
                    ->title('Selected Transactions set to Pending')
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
                    $refs = $records->pluck('reference')->toArray();

                    $records->each->update(['status' => 'Confirmed','update_by' => auth()->id()]);

                    Account_ledger::whereIn('reference', $refs)->update(['status' => 'Confirmed']);
                    Income_expense::whereIn('reference', $refs)->update(['status' => 'Confirmed']);
                });
                
                \Filament\Notifications\Notification::make()
                    ->title('Selected Transactions confirmed')
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
                    $refs = $records->pluck('reference')->toArray();

                    $records->each->update(['status' => 'Cancelled','update_by' => auth()->id()]);

                    Account_ledger::whereIn('reference', $refs)->update(['status' => 'Cancelled']);
                    Income_expense::whereIn('reference', $refs)->update(['status' => 'Cancelled']);
                });
                
                \Filament\Notifications\Notification::make()
                    ->title('Selected transactions cancelled')
                    ->danger()
                    ->send();
            }),

                    // 3. BULK CANCEL
        BulkAction::make('bulk_split')
            ->label('Split Selected')
            ->icon('heroicon-m-x-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    $refs = $records->pluck('reference')->toArray();
                     $SReferenceNo = 'STRF' . now()->format('ymdHis') . strtoupper(Str::random(9));

                    $records->each->update(['reference_no' => $SReferenceNo,'update_by' => auth()->id()]);

                    Account_ledger::whereIn('reference', $refs)->update(['reference_no' => $SReferenceNo]);
                    Income_expense::whereIn('reference', $refs)->update(['reference_no' => $SReferenceNo]);
                });
                
                \Filament\Notifications\Notification::make()
                    ->title('Selected transactions splitted')
                    ->danger()
                    ->send();
            }),

                ]), 
            ]);
    }
   protected static function calculateProfitForItem(callable $get, callable $set): void
{
    $fixed = (float) ($get('fixed_price') ?? 0);
    $sold = (float) ($get('sold_price') ?? 0);
    $from = $get('from_currency');
    $to = $get('to_currency');

    if ($fixed > 0 && $sold > 0 && $from && $to) {
        $fcur = \App\Models\Currency::find($from)?->buy_rate ?? 1;
        $tcur = \App\Models\Currency::find($to)?->sell_rate ?? 1;
        
        $profit = ($sold / $tcur) - ($fixed / $fcur);
        $set('profit', round($profit, 2));
    } else {
        $set('profit', 0); // Reset to 0 if requirements aren't met
    }
}
}