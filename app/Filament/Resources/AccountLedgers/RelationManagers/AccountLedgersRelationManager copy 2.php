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

use Filament\Support\Enums\Width;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Schemas\Components\Tabs\Tab;

use App\Models\Branch;
use App\Models\Currency;
use App\Models\Accounts;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
// use Filament\Tables\Actions\Action;
use Filament\Actions\Action;
use Filament\Tables\Actions\ExportHeaderAction; // Import this
use App\Filament\Exports\AccountLedgerExporter; // Import your exporter

use Filament\Actions\ExportAction;

class AccountLedgersRelationManager extends RelationManager
{
    protected static string $relationship = 'accountLedgers';

    // ADD THIS METHOD to disable read-only mode
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
{
    return $schema
            ->components([
                  Grid::make(12)
                    ->schema([
                           Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('account', null);
                                $set('currency', null);
                            })
                            ->searchable()
                            ->default(fn () => $this->getOwnerRecord()->branch_id)
                            ->columnSpan(6),
                         DatePicker::make('date_confirm')
                           ->label('Transaction Date')
                            ->default(now())
                            ->columnSpan(6),

             
                ])->columnSpanFull(),
                /* Row 2:  Account(4) | Currency(4) | Service(4) */
              Grid::make(12)
                    ->schema([
        /* 1. Account (Now Live) */
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
            ->live() // Crucial: This triggers the refresh for the Currency field
            ->default(fn () => $this->getOwnerRecord()->uid)
            ->afterStateUpdated(fn ($set) => $set('currency', null)) // Clear currency if account changes
            ->searchable()
            ->columnSpan(6),
                   
        /* 3. Currency (Dependent on Account) */
        Select::make('currency')
            ->label('Currency')
            ->options(function (callable $get) {
                $accountUid = $get('account');

                if (!$accountUid) {
                    return []; // Return empty if no account is selected
                }

                // Fetch the selected account to get its access_currency array
                $account = Accounts::where('uid', $accountUid)->first();

                if (!$account || empty($account->access_currency)) {
                    return [];
                }

                // access_currency is already cast to an array in your Accounts model
                return Currency::query()
                    ->whereIn('id', $account->access_currency)
                    ->where('status', true)
                    ->pluck('currency_name', 'id')
                    ->toArray();
            })
            ->searchable()
            ->required()
            ->default(fn ($livewire) => 
                            $livewire->activeTab !== 'all' ? $livewire->activeTab : $this->getOwnerRecord()->default_currency
            )
            ->columnSpan(6),
    ])
    ->columnSpanFull(),
                /* Row 3: Credit(6) | Debit(6) */
                Grid::make(12)
                    ->schema([
                        
                        TextInput::make('debit')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(6),
                        TextInput::make('credit')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(6),

                        
                    ])
                    ->columnSpanFull(), // Forces this Grid to act as a full-width row

                /* Row 4: Description (Full Width) */
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
        ]);
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
                        // Only show summary if a specific currency is filtered
                        ->hidden(fn ($livewire) => empty($livewire->tableFilters['currency']['value']))
                ),

            TextColumn::make('credit')
                ->numeric()
                ->color('success')
                ->summarize(
                    Sum::make()
                        ->label('Total Credit')
                        // Only show summary if a specific currency is filtered
                        ->hidden(fn ($livewire) => empty($livewire->tableFilters['currency']['value']))
                ),

            TextColumn::make('currencyInfo.currency_name')
                ->label('Currency')
                ->badge()
                // Show this column only when NO specific currency is filtered
                ->hidden(fn ($livewire) => !empty($livewire->tableFilters['currency']['value'])),

            TextColumn::make('balance')
                ->label('Balance')
                // 1. Hide the column entirely if multiple currencies are being shown
                ->hidden(fn ($livewire) => empty($livewire->tableFilters['currency']['value']))
                ->state(function ($record, $rowLoop, $livewire) {
                    $records = $livewire->getTableRecords();
                    // 2. Calculate running balance for the filtered currency
                    return $records->slice(0, $rowLoop->index + 1)
                        ->reduce(fn ($carry, $item) => $carry + ($item->credit - $item->debit), 0);
                })
                ->numeric()
                ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                ->summarize(
                    Summarizer::make()
                        ->label('Net Balance')
                        ->hidden(fn ($livewire) => empty($livewire->tableFilters['currency']['value']))
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
            Filter::make('date_range')
                ->schema([
                    DatePicker::make('date_confirm_from')->label('From Date'),
                    DatePicker::make('date_confirm_until')->label('Until Date'),
                ])
                ->columns(2)
                ->columnSpan(6)
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['date_confirm_from'], fn ($q, $date) => $q->whereDate('date_confirm', '>=', $date))
                        ->when($data['date_confirm_until'], fn ($q, $date) => $q->whereDate('date_confirm', '<=', $date));
                }),

            TrashedFilter::make()->columnSpan(2),

            SelectFilter::make('currency')
                ->label('Currency')
                ->options(function () {
                    $account = $this->getOwnerRecord();
                    if (!$account || empty($account->access_currency)) return [];
                    return \App\Models\Currency::query()
                        ->whereIn('id', $account->access_currency)
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
                ->columnSpan(2)

        ],
        //  layout: FiltersLayout::AboveContent
        layout: FiltersLayout::Modal
        //  layout: FiltersLayout::AboveContentCollapsible
        
        )
->deferFilters(false)// Keep this to auto-apply filters without needing an "Apply" button
        
        ->filtersFormColumns(12) 
        ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
        ->filtersFormWidth(Width::Full)
             ->headerActions([
                CreateAction::make()
                ->label('New Entry'), // No need to define ->form() here anymore

                ExportAction::make()
                    ->exporter(AccountLedgerExporter::class)
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray'),
                    // ->columnSpan(2),

              Action::make('print')
                ->label('Print Ledger')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('account_ledger.print', [
                    'ownerId' => $this->getOwnerRecord()->uid,
                    'filters' => $this->tableFilters, 
                ]))
                ->openUrlInNewTab(),

                  Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn () => route('account_ledger.print', [
                        'ownerId' => $this->getOwnerRecord()->uid,
                        'filters' => $this->tableFilters,
                        'format' => 'pdf', // Append format=pdf
                    ]))
                    ->openUrlInNewTab(),
         
                    Action::make('send_email')
                    ->label('Send Email')
                    ->icon('heroicon-o-envelope')
                    ->url(fn () => route('account_ledger.send_email', [
                        'ownerId' => $this->getOwnerRecord()->uid,
                        'filters' => $this->tableFilters,
                    ]))
                    ->openUrlInNewTab(),
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

}