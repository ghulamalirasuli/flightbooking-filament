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

    //      TextColumn::make('profit')
    // ->label('Profit')
    // ->formatStateUsing(function ($record) {
    //     return number_format($record->profit ?? 0, 2)
    //         . '<br><span class="text-gray-500 text-sm">'
    //         . ($record->profitCurrency?->currency_code ?? '-')
    //         . '</span>';
    // })
    // ->html()
    // ->summarize(
    //     Summarizer::make()
    //         ->label('Total Profit')
    //         ->using(function ($query): array {
    //             // Get the sum
    //             $total = $query->sum('profit');
                
    //             // Get distinct currencies in the current filtered set
    //             $currencyIds = $query->clone()
    //                 ->select('default_currency')
    //                 ->distinct()
    //                 ->pluck('default_currency');
                
    //             // If all records use the same currency, display it
    //             if ($currencyIds->count() === 1 && $currencyIds->first()) {
    //                 $currency = Currency::find($currencyIds->first())?->currency_code ?? '-';
    //             } else {
    //                 // Mixed currencies or no currency
    //                 $currency = $currencyIds->isEmpty() ? '-' : 'Mixed';
    //             }
                
    //             return [
    //                 'total' => $total,
    //                 'currency' => $currency,
    //             ];
    //         })
    //         ->formatStateUsing(fn (array $state): string => 
    //             number_format($state['total'], 2) . ' ' . $state['currency']
    //         )
    // ),
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
