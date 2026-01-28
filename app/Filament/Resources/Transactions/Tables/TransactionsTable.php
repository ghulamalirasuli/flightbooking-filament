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
                    
                    return "$amount $currency"; // Example Output: "500 USD"
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
                    
                    return "$amount $currency"; // Example Output: "500 USD"
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

                    TextColumn::make('profit') // Unique identifier

                    ->label('Profit')

                    ->state(fn ($record): string => $record->profitCurrency?->currency_code ?? '')

                    ->description(fn ($record): string => $record->profit ?? '0')
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

            ])->defaultSort('created_at', 'desc') // Change 'desc' to 'asc' if you want oldest first
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
                  
            ], FiltersLayout::Modal)
            ->deferFilters(false)
            ->filtersFormColumns(12)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->filtersFormWidth(Width::Full)
            ->recordActions([
               ActionGroup::make([
                // Inside your Table configuration
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
