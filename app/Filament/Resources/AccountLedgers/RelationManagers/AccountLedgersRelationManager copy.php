<?php

namespace App\Filament\Resources\AccountLedgers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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

use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Schemas\Components\Tabs\Tab;

use App\Models\Branch;
use App\Models\Currency;
use App\Models\Accounts;
use Filament\Schemas\Components\Grid;

class AccountLedgersRelationManager extends RelationManager
{
    protected static string $relationship = 'accountLedgers';

    // ADD THIS METHOD to disable read-only mode
    public function isReadOnly(): bool
    {
        return false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_no')
            ->defaultSort('date_confirm', 'asc')
            ->columns([
                TextColumn::make('date_confirm')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable(),

                TextColumn::make('description')->wrap(),

                TextColumn::make('debit')
                    ->numeric()
                    ->color('danger')
                    ->summarize(
                        Sum::make()
                            ->label('Total Debit')
                            // Hide summary when mixing currencies in "All" tab
                            ->hidden(fn ($livewire) => $livewire->activeTab === 'all' || $livewire->activeTab === null)
                    ),

                TextColumn::make('credit')
                    ->numeric()
                    ->color('success')
                    ->summarize(
                        Sum::make()
                            ->label('Total Credit')
                            ->hidden(fn ($livewire) => $livewire->activeTab === 'all' || $livewire->activeTab === null)
                    ),

                // 1. FIXED: Restored Currency Column
                TextColumn::make('currencyInfo.currency_name')
                    ->label('Currency')
                    ->badge()
                    // Hide this column when a specific currency tab is already selected
                    ->hidden(fn ($livewire) => $livewire->activeTab !== 'all' && $livewire->activeTab !== null),

                TextColumn::make('balance')
                    ->label('Running Balance')
                    // 2. FIXED: Hide Running Balance column in the "All" tab to prevent confusion
                    ->hidden(fn ($livewire) => $livewire->activeTab === 'all' || $livewire->activeTab === null)
                    ->state(function ($record, $rowLoop, $livewire) {
                        $records = $livewire->getTableRecords();
                        return $records->slice(0, $rowLoop->index + 1)
                            ->reduce(fn ($carry, $item) => $carry + ($item->credit - $item->debit), 0);
                    })
                    ->numeric()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->summarize(
                        Summarizer::make()
                            ->label('Net Balance')
                            ->hidden(fn ($livewire) => $livewire->activeTab === 'all' || $livewire->activeTab === null)
                            ->using(fn ($query) => $query->sum('credit') - $query->sum('debit'))
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Confirmed' => 'success',
                        'Pending' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                 TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'Confirmed' => 'Confirmed',
                        'Pending' => 'Pending',
                        'Cancelled' => 'Cancelled',
                    ]),
            ])
             ->headerActions([
   CreateAction::make()
        ->label('New Entry')
        ->modalHeading('Create New Transaction')
        ->form([
            // ROW 1: Branch and Date
            Grid::make(12)->schema([
                Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                    ->live()
                    ->default(fn () => $this->getOwnerRecord()->branch_id) // Pre-selects the Account's branch
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
                    ->default(fn () => $this->getOwnerRecord()->uid) // Pre-selects the Current Account
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
                    // Pre-selects the currency based on the active Tab or Account default
                    ->default(fn ($livewire) => 
                        $livewire->activeTab !== 'all' ? $livewire->activeTab : $this->getOwnerRecord()->default_currency
                    ) 
                    ->searchable()
                    ->required()
                    ->columnSpan(6),
            ]),

            // ROW 3: Debit and Credit
            Grid::make(12)->schema([
                TextInput::make('debit')->numeric()->default(0)->columnSpan(6)->requiredWithout('credit'),
                TextInput::make('credit')->numeric()->default(0)->columnSpan(6)->requiredWithout('debit'),
            ]),

            // ROW 4: Description
            Textarea::make('description')->rows(3)->columnSpanFull(),
        ])
])
        ->actions([
            ActionGroup::make([
                ViewAction::make()->visible(true),
                EditAction::make()->visible(true),
                DeleteAction::make()->visible(true),
                ForceDeleteAction::make()->label('Delete forever'),
                RestoreAction::make(),
            ])->icon('heroicon-m-ellipsis-vertical'),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
    }

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('All Currencies')];

        $authCurrencies = $this->getOwnerRecord()->access_currency ?? [];
        $currencies = \App\Models\Currency::whereIn('id', $authCurrencies)->get();

        foreach ($currencies as $currency) {
            $tabs[$currency->id] = Tab::make($currency->currency_name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('currency', $currency->id));
        }

        return $tabs;
    }
}