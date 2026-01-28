<?php

namespace App\Filament\Resources\Deposits\Tables;
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
use App\Models\CashBox;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;// Like Ajax show and hide content
use Illuminate\Support\HtmlString;// Like Ajax show and hide content

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
                    
               TextColumn::make('from_account')
    ->label('Account')
    ->formatStateUsing(function ($state, $record) {
        // 1. Check if the relationship actually loaded an Account model
        $account = $record->account;

        if ($account) {
            // Use your model's custom attribute for the formatted name
            return $account->account_name_with_category_and_branch;
        }

        // 2. If no account relationship, return the raw text (e.g., "Exchange Currency")
        // or a default placeholder if the state is empty
        return $state ?? 'N/A';
    })
    ->searchable(query: function ($query, string $search) {
        $query->where(function ($q) use ($search) {
            // Search the raw text field itself (covers "Exchange Currency")
            $q->where('from_account', 'like', "%{$search}%")
              // Also search through the relationship if it exists
              ->orWhereHas('account', function ($sub) use ($search) {
                  $sub->where('account_name', 'like', "%{$search}%")
                      ->orWhereHas('accountType', fn($inner) => $inner->where('accounts_category', 'like', "%{$search}%"))
                      ->orWhereHas('branch', fn($inner) => $inner->where('branch_name', 'like', "%{$search}%"));
              });
        });
    }),
                TextColumn::make('entry_type')
                    ->label('Type')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),

                // First Column
                TextColumn::make('debit_display') // Unique identifier
                    ->label('Debit')
                    ->state(fn ($record): string => $record->currency?->currency_code ?? '') 
                    ->description(fn ($record): string => $record->debit ?? '0'),

                // Second Column
                TextColumn::make('credit_display') // Unique identifier
                    ->label('Credit')
                    ->state(fn ($record): string => $record->currency?->currency_code ?? '')
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
        // DepositsTable.php inside headerActions
Action::make('print_pdf')
    ->label('Download PDF')
    ->icon('heroicon-o-arrow-down-tray')
    ->color('success')
    ->url(function ($livewire) {
        $filters = $livewire->tableFilters;
        return route('deposits.print_all', [
            'filters' => $filters,
            'format' => 'pdf' // Add this flag
        ]);
    })
    ->openUrlInNewTab(),
    
Action::make('print_all')
    ->label('Print All Deposits')
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

        return route('deposits.print_all', $params);
    })
    ->openUrlInNewTab(),
                // Add the Exchange action here
Action::make('exchange')
    ->label('Currency Exchange')
    ->icon('heroicon-o-arrows-right-left')
    ->color('info')
    ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
    ->form([ 
        Section::make()
        // ----------------- Exchange Form-----------
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
    
    // 3. Update signature to include $action, $form, and $arguments
    ->action(function (array $data, Action $action, $form, array $arguments): void {
        DB::transaction(function () use ($data) {
                        $reference_no = 'EXR-' . now()->format('ymdhis');

                        $references = CashBox::select(['cash_box.*'])->count();
        // $code = Branch::where('uid', Auth::guard('web')->user()->branch_id)->first();
        $code = Branch::where('id',$data['branch_id'])->first();
        $reference = "EXL".date('ymdhis').$references+1;

        $reference_no = "EXLO".$code->branch_code.date('ymdhis');
        $uid = "EXLU".$code->branch_code.date('ymdhis');

                        
                        // 1. STORE THE SELL ENTRY (DEBIT)
                        CashBox::create([
                            'uid'           => $uid,
                            'from_account'  =>'Exchange Currency',
                            'amount_from'   =>$data['buy_amount'],
                            'currency_from' =>$data['buy_currency'],
                            'reference_no'  => $reference_no,
                            'reference'     => $reference,
                            'branch_id'     => $data['branch_id'],
                            'user_id'       => auth()->id(),
                            'currency_id'   => $data['sell_currency'],
                            'debit'         => $data['sell_amount'],
                            'credit'        => 0,
                            'status'        => "Pending",
                            'entry_type'    => "Exchange",
                            'description'   => $data['description'] ?? 'Currency Exchange Sell',
                            'date_confirm'  => now()->format('Y-m-d'),
                            'date_update'   => now()->format('Y-m-d'),
                        ]);

                        // 2. STORE THE BUY ENTRY (CREDIT)
                        CashBox::create([
                            'uid'           => $uid,
                            'from_account'  =>'Exchange Currency',
                            'amount_from'   =>$data['sell_amount'],
                            'currency_from' =>$data['sell_currency'],
                            'reference_no'  => $reference_no,
                            'reference'     => $reference,
                            'branch_id'     => $data['branch_id'],
                            'user_id'       => auth()->id(),
                            'currency_id'   => $data['buy_currency'],
                            'debit'         => 0,
                            'credit'        => $data['buy_amount'],
                            'status'        => "Pending",
                            'entry_type'    => "Exchange",
                            'description'   => $data['description'] ?? 'Currency Exchange Buy',
                            'date_confirm'  => now()->format('Y-m-d'),
                            'date_update'   => now()->format('Y-m-d'),
                        ]);
                    });

                    Notification::make()
                        ->title('Exchange Completed successfully')
                        ->success()
                        ->send();
                        // Now these variables will work
     // Check if "Save & New" was clicked
        if ($arguments['another'] ?? false) {
            $form->fill(); 
            $action->halt(); 
        }
                }),
            // Your existing New Deposit action

                Action::make('create_deposit')
                    ->label('New Deposit')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('New Deposit')
                     // --------------- Deposit Form---------
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

                                 Group::make([
                                Select::make('from_account')
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
                                ->afterStateUpdated(fn ($set) => $set('currency_id', null))
                                ->searchable(),
                                

                                  Placeholder::make('from_balance_preview')
                                ->hiddenLabel()
                                ->visible(fn ($get) => filled($get('from_account'))) // Fixes the empty space issue
                                ->content(fn ($get) => view('filament.components.account-balance-table', [
                                    'accountUid' => $get('from_account')
                                ])),
                        ])->columnSpan(6),
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
   ->modalSubmitAction(false) //disable default submit
    ->extraModalFooterActions(fn (Action $action): array => [
        $action->makeModalSubmitAction('saveAndNew', arguments: ['another' => true])
            ->label('Save & New')
            ->color('gray'),
        $action->makeModalSubmitAction('save')
            ->label('Save')
            ->color('warning'), 
    ])
    ->action(function (array $data, Action $action, $form, array $arguments) {
        DB::transaction(function () use ($data) {
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

                        });
                        Notification::make()->title('Deposit Saved')->success()->send();
                        // Now these variables will work
                if ($arguments['another'] ?? false) {
                            // Tip: We keep the branch_id so the user doesn't have to re-select it
                            $form->fill(['branch_id' => $data['branch_id']]); 
                            $action->halt(); 
                        }
                    })
            ])->deferColumnManager(false)
            ->columnManagerResetActionPosition(ColumnManagerResetActionPosition::Footer)
    // -------------- Filters------------
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
                 SelectFilter::make('entry_type')
                        ->options([
                            'Debit' => 'Debit',
                            'Credit' => 'Credit',
                            'Exchange' => 'Exchange',
                        ])
                    ->columnSpan(2),
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
                // $record->update(['status' => 'Confirmed','update_by' => auth()->id()]); // single row by ID
                // 1. Update ALL rows in CashBox with this reference number
            \App\Models\CashBox::where('reference_no', $record->reference_no)
                ->update([
                    'status' => 'Confirmed',
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);

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
                // $record->update(['status' => 'Cancelled','update_by' => auth()->id()]);

                   \App\Models\CashBox::where('reference_no', $record->reference_no)
                ->update([
                    'status' => 'Cancelled',
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);
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
                // $record->update(['status' => 'Pending','update_by' => auth()->id()]);
                   \App\Models\CashBox::where('reference_no', $record->reference_no)
                ->update([
                    'status' => 'Pending',
                    'update_by' => auth()->id(),
                    'date_update' => now()->format('Y-m-d'),
                ]);

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
            ViewAction::make()
    ->modalHeading(fn ($record) => $record->entry_type === 'Exchange' ? 'View Exchange Details' : 'View Deposit Details')
    ->infolist(function ($record): array {
        // --- 1. VIEW FOR EXCHANGE TYPE ---
        if ($record->entry_type === 'Exchange') {
            // 1. Fetch the paired record (The other side of the exchange)
            $related = \App\Models\CashBox::where('uid', $record->uid)
                ->where('id', '!=', $record->id)
                ->first();

            // 2. Identify which row is Selling (Debit) and which is Buying (Credit)
            $sellRow = $record->debit > 0 ? $record : $related;
            $buyRow = $record->credit > 0 ? $record : $related;

            // 3. Calculate Transaction Rate: (Buy Amount / Sell Amount)
            // This represents the actual rate used during the transaction.
            $transactionRate = ($sellRow && $buyRow && $sellRow->debit > 0) 
                ? round($buyRow->credit / $sellRow->debit, 4) 
                : 0;

            // 4. (Optional) Fetch Official Rate from Currency Table for comparison
            $officialRate = $sellRow?->currency?->sell_rate ?? 'N/A';

            return [
                \Filament\Schemas\Components\Section::make('Exchange Information')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(3)->schema([
                            \Filament\Infolists\Components\TextEntry::make('branch.branch_name')->label('Branch'),
                            \Filament\Infolists\Components\TextEntry::make('reference_no')->label('Reference'),
                            \Filament\Infolists\Components\TextEntry::make('status')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'Confirmed' => 'success',
                                    'Pending' => 'warning',
                                    'Cancelled' => 'danger',
                                    default => 'gray',
                                }),
                        ]),
                        \Filament\Schemas\Components\Grid::make(3)->schema([
                            \Filament\Infolists\Components\TextEntry::make('sell_info')
                                ->label('Selling')
                                ->state(fn() => $sellRow ? "{$sellRow->debit} " . ($sellRow->currency?->currency_name ?? '') : 'N/A')
                                ->color('danger')
                                ->weight('bold'),
                                
    \Filament\Infolists\Components\TextEntry::make('exchange_rate')
    ->label('Applied Rate')
    ->state(function ($record) { // Changed from fn() => { to function ($record) {
        // 1. Fetch the paired record using the shared UID
        $related = \App\Models\CashBox::where('uid', $record->uid)
            ->where('id', '!=', $record->id)
            ->first();

        if (!$related) return 'N/A';

        // 2. Identify Sell (Debit) and Buy (Credit) amounts
        $sellAmount = $record->debit > 0 ? $record->debit : $related->debit;
        $buyAmount = $record->credit > 0 ? $record->credit : $related->credit;

        if ($sellAmount <= 0 || $buyAmount <= 0) return '0';

        // 3. Logic: Divide larger by smaller to get the readable rate (e.g., 66.00)
        $rate = ($sellAmount > $buyAmount) 
            ? ($sellAmount / $buyAmount) 
            : ($buyAmount / $sellAmount);

        return number_format($rate, 2);
    })
    ->icon('heroicon-m-arrows-right-left')
    ->color('info'),

                            \Filament\Infolists\Components\TextEntry::make('buy_info')
                                ->label('Buying')
                                ->state(fn() => $buyRow ? "{$buyRow->credit} " . ($buyRow->currency?->currency_name ?? '') : 'N/A')
                                ->color('success')
                                ->weight('bold'),
                        ]),
                        \Filament\Infolists\Components\TextEntry::make('description')->columnSpanFull(),
                    ])
            ];
        }

        // --- 2. VIEW FOR STANDARD DEPOSIT ---
        return [
            \Filament\Schemas\Components\Section::make('Deposit Information')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(12)->schema([
                        \Filament\Infolists\Components\TextEntry::make('branch.branch_name')
                            ->label('Branch')
                            ->columnSpan(6),
                        \Filament\Infolists\Components\TextEntry::make('account_display')
                            ->label('Account')
                            ->state(fn ($record) => $record->account?->account_name_with_category_and_branch ?? $record->from_account)
                            ->columnSpan(6),
                    ]),
                    \Filament\Schemas\Components\Grid::make(12)->schema([
                        \Filament\Infolists\Components\TextEntry::make('entry_type')
                            ->label('Type')
                            ->badge()
                            ->color(fn ($state) => $state === 'Debit' ? 'info' : 'warning')
                            ->columnSpan(4),
                        \Filament\Infolists\Components\TextEntry::make('currency.currency_name')
                            ->label('Currency')
                            ->columnSpan(4),
                        \Filament\Infolists\Components\TextEntry::make('amount_from')
                            ->label('Amount')
                            ->numeric()
                            ->weight('bold')
                            ->columnSpan(4),
                    ]),
                    \Filament\Schemas\Components\Grid::make(12)->schema([
                        \Filament\Infolists\Components\TextEntry::make('reference_no')->label('Reference')->columnSpan(4),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Confirmed' => 'success',
                                'Pending' => 'warning',
                                'Cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->columnSpan(4),
                        \Filament\Infolists\Components\TextEntry::make('user.name')->label('Created By')->columnSpan(4),
                    ]),
                    \Filament\Infolists\Components\TextEntry::make('description')->columnSpanFull(),
                ])
        ];
    }),
        
        // EditAction::make()
        //     ->modalHeading('Edit Deposit'),

       EditAction::make('edit_deposit')
    ->label('Edit')
    ->icon('heroicon-m-pencil-square')
    ->modalHeading('Edit Deposit')
    ->visible(fn ($record) => $record->entry_type !== 'Exchange')
    ->form([
        Section::make('Deposit Details')
            ->schema([
                Grid::make(12)->schema([
                    Select::make('branch_id')
                        ->label('Branch')
                        ->options(\App\Models\Branch::where('status', true)->pluck('branch_name', 'id'))
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('from_account', null);
                            $set('currency_id', null);
                        })
                        ->columnSpan(6),

                    Select::make('from_account')
                        ->label('Account')
                        ->options(function (callable $get, $record) {
                            // Use state from form, fallback to the record's branch if form state is empty
                            $branchId = $get('branch_id') ?? $record->branch_id;
                            
                            if (!$branchId) return [];

                            return \App\Models\Accounts::query()
                                ->with(['accountType', 'branch'])
                                ->where('is_active', true)
                                ->where('branch_id', $branchId)
                                ->get()
                                ->mapWithKeys(function ($account) {
                                    $name = $account->account_name;
                                    $category = $account->accountType?->accounts_category ?? 'N/A';
                                    $branch = $account->branch?->branch_name ?? 'N/A';
                                    return [$account->uid => "({$branch}) {$name} - {$category}"];
                                });
                        })
                        ->live()
                        ->required()
                        ->afterStateUpdated(fn ($set) => $set('currency_id', null))
                        ->columnSpan(6),
                ]),
                Grid::make(12)->schema([
                    Select::make('entry_type')
                        ->label('Deposit Type')
                        ->options(['Debit' => 'Debit', 'Credit' => 'Credit'])
                        ->required()
                        ->columnSpan(4),

                    Select::make('currency_id')
                        ->label('Currency')
                        ->options(function (callable $get, $record) {
                            // Use state from form, fallback to the record's account if form state is empty
                            $accountUid = $get('from_account') ?? $record->from_account;
                            
                            if (!$accountUid) return [];

                            $account = \App\Models\Accounts::where('uid', $accountUid)->first();
                            if (!$account || empty($account->access_currency)) return [];

                            return \App\Models\Currency::whereIn('id', $account->access_currency)
                                ->where('status', true)
                                ->pluck('currency_name', 'id');
                        })
                        ->required()
                        ->columnSpan(4),

                    TextInput::make('amount_from')
                        ->label("Amount")
                        ->numeric()
                        ->required()
                        ->columnSpan(4),
                ]),
                Textarea::make('description')->rows(3)->columnSpanFull(),
            ])
    ])
    // Ensure data is saved to both CashBox and Account_ledger
    ->action(function ($record, array $data): void {
        \DB::transaction(function () use ($record, $data) {
            // 1. Update the Deposit Record (CashBox)
            $record->update([
                'branch_id'     => $data['branch_id'],
                'from_account'   => $data['from_account'],
                'entry_type'     => $data['entry_type'],
                'currency_id'    => $data['currency_id'],
                'currency_from'  => $data['currency_id'],
                'amount_from'    => $data['amount_from'],
                'debit'          => $data['entry_type'] === 'Debit' ? $data['amount_from'] : 0,
                'credit'         => $data['entry_type'] === 'Credit' ? $data['amount_from'] : 0,
                'description'    => $data['description'],
                'date_update'    => now()->format('Y-m-d'),
            ]);

            // 2. Update the corresponding Ledger Record
            \App\Models\Account_ledger::where('reference_no', $record->reference_no)->update([
                'branch_id'   => $data['branch_id'],
                'account'     => $data['from_account'],
                'currency'    => $data['currency_id'],
                'debit'       => $data['entry_type'] === 'Debit' ? $data['amount_from'] : 0,
                'credit'      => $data['entry_type'] === 'Credit' ? $data['amount_from'] : 0,
                'description' => $data['description'],
                'date_update' => now()->format('Y-m-d'),
            ]);
        });

        \Filament\Notifications\Notification::make()
            ->title('Deposit updated successfully')
            ->success()
            ->send();
    }),
    // 2. ACTION FOR EXCHANGES
    EditAction::make('edit_exchange')
    ->label('Edit')
    ->icon('heroicon-m-pencil-square')
    ->modalHeading('Edit Exchange')
    ->visible(fn ($record) => $record->entry_type === 'Exchange')
    ->fillForm(function ($record): array {
        // 1. Fetch the paired record
        $related = \App\Models\CashBox::where('uid', $record->uid)
            ->where('id', '!=', $record->id)
            ->first();

        $sellRow = $record->debit > 0 ? $record : $related;
        $buyRow = $record->credit > 0 ? $record : $related;

        // 2. Logic: Infer the human-readable rate (e.g., 66.00)
        // Divide larger amount by smaller amount to match what the user originally typed
        $sellAmt = $sellRow?->debit ?? 0;
        $buyAmt = $buyRow?->credit ?? 0;
        
        $rate = 1;
        if ($sellAmt > 0 && $buyAmt > 0) {
            $rate = ($sellAmt > $buyAmt) ? ($sellAmt / $buyAmt) : ($buyAmt / $sellAmt);
        }

        return [
            'branch_id'     => $record->branch_id,
            'sell_currency' => $sellRow?->currency_id,
            'buy_currency'  => $buyRow?->currency_id,
            'sell_amount'   => $sellAmt,
            'buy_amount'    => $buyAmt,
            'description'   => $record->description,
            'rate'          => round($rate, 4),
            'divmul'        => ($sellAmt > $buyAmt) ? 'Divide' : 'Multiply', // Auto-detect the action
        ];
    })
    ->form([
        Section::make('Exchange Details')
            ->schema([
                Grid::make(12)->schema([
                    Select::make('branch_id')
                        ->label('Branch')
                        ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                        ->live()
                        ->required()
                        ->columnSpan(12),
                    Select::make('sell_currency')
                        ->label('Sell Currency')
                        ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                        ->live()
                        ->required()
                        ->columnSpan(4),
                    Select::make('divmul')
                        ->label('Action')
                        ->options(['Multiply' => 'Multiply (*)', 'Divide' => 'Divide (/)'])
                        ->live()
                        ->columnSpan(4),
                    Select::make('buy_currency')
                        ->label('Buy Currency')
                        ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                        ->live()
                        ->required()
                        ->columnSpan(4),
                    TextInput::make('sell_amount')
                        ->label('Sell Amount (Debit)')
                        ->numeric()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($get, $set) => self::updateExchangeAmounts($get, $set))
                        ->columnSpan(4),
                    TextInput::make('rate')
                        ->label('Exchange Rate')
                        ->numeric()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($get, $set) => self::updateExchangeAmounts($get, $set))
                        ->columnSpan(4),
                    TextInput::make('buy_amount')
                        ->label('Buy Amount (Credit)')
                        ->numeric()
                        ->columnSpan(4),
                    Textarea::make('description')->columnSpanFull(),
                ]),
            ])
    ])
    ->action(function ($record, array $data): void {
        DB::transaction(function () use ($record, $data) {
            // 1. Update the Sell side (Debit row)
            \App\Models\CashBox::where('uid', $record->uid)
                ->where('debit', '>', 0)
                ->update([
                    'branch_id'   => $data['branch_id'],
                    'currency_id' => $data['sell_currency'],
                    'debit'       => $data['sell_amount'],
                    'amount_from' => $data['buy_amount'], // Inverted for exchange logic
                    'description' => $data['description'],
                    'update_by'   => auth()->id(),
                ]);

            // 2. Update the Buy side (Credit row)
            \App\Models\CashBox::where('uid', $record->uid)
                ->where('credit', '>', 0)
                ->update([
                    'branch_id'   => $data['branch_id'],
                    'currency_id' => $data['buy_currency'],
                    'credit'      => $data['buy_amount'],
                    'amount_from' => $data['sell_amount'], // Inverted for exchange logic
                    'description' => $data['description'],
                    'update_by'   => auth()->id(),
                ]);
        });

        Notification::make()->title('Exchange Updated Successfully')->success()->send();
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
