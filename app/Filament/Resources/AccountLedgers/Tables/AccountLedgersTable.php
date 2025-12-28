<?php

namespace App\Filament\Resources\AccountLedgers\Tables;

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

use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\Account_ledger;
use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Branch;


use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

use Filament\Tables\Filters\SelectFilter;

use Filament\Notifications\Notification;

class AccountLedgersTable
{
   public static function configure(Table $table): Table
    {
        $currencies = Currency::where('status', 1)->get();

        return $table
            ->columns([
                TextColumn::make('account_name_with_category_and_branch')
                    ->label('Account Name')
                    ->searchable(['account_name'])
                    ->sortable(),

                // Dynamic Balance Columns
                ...$currencies->map(function (Currency $currency) {
                    return TextColumn::make('balance_' . $currency->currency_code)
                        // ->label($currency->currency_code . ' Balance')
                        ->label($currency->currency_code)
                        ->getStateUsing(function ($record) use ($currency) {
                            $totals = Account_ledger::query()
                                ->where('account', $record->uid)
                                ->where('currency', $currency->id)
                                ->where('status', '=','Confirmed')
                                ->selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit')
                                ->first();

                            $balance = ($totals->total_credit ?? 0) - ($totals->total_debit ?? 0);
                            return number_format($balance, 2) . ' ' . $currency->currency_code;
                        })
                        ->badge()
                        ->color(fn ($state) => str_contains($state, '-') ? 'danger' : 'success');
                })->toArray(),
            ])
            ->headerActions([
                Action::make('create_ledger')
                    ->label('New Ledger Entry')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Create New Transaction')
                    ->form([
                        // ROW 1: Branch and Date
                        Grid::make(12)->schema([
                            Select::make('branch_id')
                                ->label('Branch')
                                ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('account', null);
                                    $set('currency', null);
                                })
                                ->searchable()
                                ->columnSpan(6),
                            DatePicker::make('date_confirm')
                                ->label('Transaction Date')
                                ->default(now())
                                ->required()
                                ->columnSpan(6),
                        ]),

                        // ROW 2: Account and Currency (DEPENDENT)
                        Grid::make(12)->schema([
                            Select::make('account')
                                ->label('Account')
                                ->options(function (callable $get) {
                                    $branchId = $get('branch_id');
                                    return Accounts::query()
                                        ->where('is_active', true)
                                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                        ->get()
                                        ->pluck('account_name', 'uid');
                                })
                                ->live()
                                ->afterStateUpdated(fn ($set) => $set('currency', null))
                                ->searchable()
                                ->required()
                                ->columnSpan(6),

                            Select::make('currency')
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
                                ->columnSpan(6),
                        ]),

                        // ROW 3: Debit and Credit
                        Grid::make(12)->schema([
                            TextInput::make('debit')->numeric()->default(0)->columnSpan(6),
                            TextInput::make('credit')->numeric()->default(0)->columnSpan(6),
                        ]),

                        // ROW 4: Description
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        // This manually inserts into account_ledger to avoid the SQL error
                        Account_ledger::create([
                            'branch_id' => $data['branch_id'],
                            'account' => $data['account'],
                            'currency' => $data['currency'],
                            'date_confirm' => $data['date_confirm'],
                            'debit' => $data['debit'],
                            'credit' => $data['credit'],
                            'description' => $data['description'],
                            'status' => 1,
                            'user_id' => auth()->id(),
                        ]);

                        Notification::make()->title('Transaction Saved')->success()->send();
                    })
            ])
            ->actions([
                ViewAction::make()->label('View Ledger'),
            ]);
    }
}
