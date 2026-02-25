<?php

namespace App\Filament\Resources\Expenses\Tables;

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

use Filament\Tables\Enums\ColumnManagerResetActionPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable; // Add this to your imports at the top


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;// Like Ajax show and hide content
use Filament\Support\Enums\Width;

use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;// Like Ajax show and hide content
use Filament\Forms\Components\DatePicker;

use App\Models\Expense;
use App\Models\Account_ledger;
use App\Models\Branch;
use App\Models\CashBox;
use App\Models\Income_expense;

class ExpensesTable
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
                    ->searchable(),

                   TextColumn::make('expenseType.expensetype') // Shows the name instead of the ID
                    ->label('Expense type')
                    ->searchable()
                    ->sortable(),

                
               TextColumn::make('accountExp.account_name')
                    ->label('Account')
                    // This replaces the "Ahmad" with the "Ahmad - Category (Branch)" version
                    ->formatStateUsing(fn ($record) => $record->accountExp?->account_name_with_category_and_branch ?? 'N/A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entry_type')
                    ->searchable(),
                    
             
              TextColumn::make('amount')
                    ->label('Amount')
                    ->state(function ($record): float {
                        // Correctly pulls Credit or Debit based on entry_type
                        return $record->entry_type === 'Credit' ? $record->credit : $record->debit;
                    })
                    ->numeric() // Keeps the number formatting (commas, decimals)
                    ->description(fn ($record) => $record->currencyExp?->currency_code ?? 'N/A') // Shows currency below
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        // This allows searching by the raw credit or debit numbers
                        return $query->where('credit', 'like', "%{$search}%")
                                    ->orWhere('debit', 'like', "%{$search}%");
                    }),

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

                TextColumn::make('date_confirm')
                    ->date()
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_update')
                    ->date()
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_by.name')->label('Updated By')
                    ->searchable()
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
              ->filters([
             Filter::make('date_range')
                    ->schema([
                        DatePicker::make('date_confirm_from')->label('From Date'),
                        DatePicker::make('date_confirm_until')->label('To Date'),
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
                        ])
                    ->columnSpan(2),
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
                  
            ], FiltersLayout::Modal)
            ->deferFilters(false)
            ->filtersFormColumns(12)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->filtersFormWidth(Width::Full)
                 ->headerActions([
                // Moves the "New Expense" button inside the table card
                CreateAction::make()
                    ->label('New Expense')
                    ->icon('heroicon-o-plus-circle')
                    ->button(),

                // Print Action: Passes current filters/search to a custom route
               Action::make('print')
                ->label('Print Report')
                ->icon('heroicon-o-printer')
                ->color('gray')
                // Get the active filter state from the Livewire component and pass it to the route
                ->url(fn (HasTable $livewire) => route('expenses.print', ['filters' => $livewire->tableFilters]), shouldOpenInNewTab: true)
                ->visible(fn () => !request()->has('trashed')),
            ])
             ->recordActions([
               ActionGroup::make([
               
                EditAction::make(),

              // --- CONFIRM ACTION ---
Action::make('confirm')
    ->label('Confirm')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->requiresConfirmation()
    ->visible(fn (Expense $record) => $record->status !== 'Confirmed')
    ->action(function (Expense $record) {
        DB::transaction(function () use ($record) {
            $record->update([
                'status' => 'Confirmed',
                'update_by' => auth()->id(), // Sets current user
                'date_update' => now(),      // Updates date field
            ]);
            
            Account_ledger::where('reference_no', $record->reference_no)
                ->update(['status' => 'Confirmed']);
        });
        Notification::make()->title('Expense Confirmed')->success()->send();
    }),

// --- CANCEL ACTION ---
Action::make('cancel')
    ->label('Cancel')
    ->icon('heroicon-o-x-circle')
    ->color('danger')
    ->requiresConfirmation()
    ->visible(fn (Expense $record) => $record->status !== 'Cancelled')
    ->action(function (Expense $record) {
        DB::transaction(function () use ($record) {
            $record->update([
                'status' => 'Cancelled',
                'update_by' => auth()->id(), // Sets current user
                'date_update' => now(),
            ]);

            Account_ledger::where('reference_no', $record->reference_no)
                ->update(['status' => 'Cancelled']);
        });
        Notification::make()->title('Expense Cancelled')->danger()->send();
    }),

// --- PENDING ACTION ---
Action::make('set_pending')
    ->label('Set Pending')
    ->icon('heroicon-m-pause-circle')
    ->color('warning')
    ->visible(fn (Expense $record) => $record->status !== 'Pending')
    ->action(function (Expense $record) {
        DB::transaction(function () use ($record) {
            $record->update([
                'status' => 'Pending',
                'update_by' => auth()->id(), // Sets current user
                'date_update' => now(),
            ]);

            Account_ledger::where('reference_no', $record->reference_no)
                ->update(['status' => 'Pending']);
        });
        Notification::make()->title('Status set to Pending')->warning()->send();
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
                Notification::make()->title('Expense and Ledger moved to trash')->success()->send();
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
                Notification::make()->title('Expense and Ledger restored')->success()->send();
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
                Notification::make()->title('Expense and Ledger deleted permanently')->danger()->send();
            }),

               ])
            ])
        ->toolbarActions([
    BulkActionGroup::make([
        // --- BULK CONFIRM ---
      BulkAction::make('bulk_confirm')
       ->label('Set Pending Selected')
            ->icon('heroicon-m-check-badge')
            ->color('success')
    ->action(function (Collection $records) {
        DB::transaction(function () use ($records) {
            foreach ($records as $record) {
                $record->update([
                    'status' => 'Confirmed',
                    'update_by' => auth()->id(),
                    'date_update' => now(),
                ]);
                Account_ledger::where('reference_no', $record->reference_no)
                    ->update(['status' => 'Confirmed']);
            }
        });
        Notification::make()->title('Selected Expenses Confirmed')->success()->send();
    }),


                  // --- BULK Pending ---
        BulkAction::make('bulk_pending')
            ->label('Set Pending Selected')
            ->icon('heroicon-o-pause-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    foreach ($records as $record) {
                         $record->update([
                    'status' => 'Pending',
                    'update_by' => auth()->id(),
                    'date_update' => now(),
                ]);
                        Account_ledger::where('reference_no', $record->reference_no)
                            ->update(['status' => 'Pending']);
                    }
                });
                Notification::make()->title('Selected Expenses set to Pending')->success()->send();
            }),

        // --- BULK CANCEL ---
        BulkAction::make('bulk_cancel')
            ->label('Cancel Selected')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    foreach ($records as $record) {
                         $record->update([
                    'status' => 'Cancelled',
                    'update_by' => auth()->id(),
                    'date_update' => now(),
                ]);
                        Account_ledger::where('reference_no', $record->reference_no)
                            ->update(['status' => 'Cancelled']);
                    }
                });
                Notification::make()->title('Selected Expenses Cancelled')->danger()->send();
            }),

        // --- BULK DELETE (SYNCED) ---
        DeleteBulkAction::make()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    foreach ($records as $record) {
                        // Soft delete ledger first
                        Account_ledger::where('reference_no', $record->reference_no)->delete();
                        // Soft delete expense
                        $record->delete();
                    }
                });
                Notification::make()->title('Selected records moved to trash')->success()->send();
            }),

        // --- BULK RESTORE (SYNCED) ---
        RestoreBulkAction::make()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    foreach ($records as $record) {
                        Account_ledger::withTrashed()
                            ->where('reference_no', $record->reference_no)
                            ->restore();
                        $record->restore();
                    }
                });
                Notification::make()->title('Selected records restored')->success()->send();
            }),

        // --- BULK FORCE DELETE (SYNCED) ---
        ForceDeleteBulkAction::make()
            ->action(function (Collection $records) {
                DB::transaction(function () use ($records) {
                    foreach ($records as $record) {
                        Account_ledger::withTrashed()
                            ->where('reference_no', $record->reference_no)
                            ->forceDelete();
                        $record->forceDelete();
                    }
                });
                Notification::make()->title('Selected records permanently deleted')->danger()->send();
            }),
    ]),
]);
    }
}
