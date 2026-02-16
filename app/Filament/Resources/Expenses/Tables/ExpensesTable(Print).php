<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Models\Expense;
use App\Models\Account_ledger;
use App\Models\Branch;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;

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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Inserted')
                    ->description(fn ($record): string => $record->created_at?->format('M d, Y H:i') ?? 'N/A')
                    ->searchable(),
                
                TextColumn::make('accountExp.account_name')
                    ->label('Account')
                    ->formatStateUsing(fn ($record) => $record->accountExp?->account_name_with_category_and_branch ?? 'N/A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->state(function ($record): float {
                        return $record->entry_type === 'Credit' ? $record->credit : $record->debit;
                    })
                    ->numeric()
                    ->description(fn ($record) => $record->currencyExp?->currency_name ?? 'N/A')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmed' => 'success',
                        'Pending' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->description(function ($record): ?string {
                        if (!$record->updated_by || !$record->updated_by->name) return null;
                        $date = $record->updated_at?->format('M d, Y H:i') ?? 'N/A';
                        return "{$date} By {$record->updated_by->name}";
                    }),

                TextColumn::make('reference_no')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
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
                    // ->url(fn () => route('expenses.print', request()->all()), shouldOpenInNewTab: true)
                    ->visible(fn () => !request()->has('trashed')),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    ViewAction::make(),
                    
                    // --- Status Change Actions ---
                    Action::make('confirm')
                        ->label('Confirm')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Expense $record) => $record->status !== 'Confirmed')
                        ->action(function (Expense $record) {
                            DB::transaction(function () use ($record) {
                                $record->update(['status' => 'Confirmed']);
                                Account_ledger::where('reference_no', $record->reference_no)->update(['status' => 'Confirmed']);
                            });
                            Notification::make()->title('Confirmed')->success()->send();
                        }),

                    Action::make('cancel')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Expense $record) => $record->status !== 'Cancelled')
                        ->action(function (Expense $record) {
                            DB::transaction(function () use ($record) {
                                $record->update(['status' => 'Cancelled']);
                                Account_ledger::where('reference_no', $record->reference_no)->update(['status' => 'Cancelled']);
                            });
                            Notification::make()->title('Cancelled')->danger()->send();
                        }),

                    // --- Synced Delete Actions ---
                    DeleteAction::make()
                        ->action(function ($record) {
                            DB::transaction(function () use ($record) {
                                Account_ledger::where('reference_no', $record->reference_no)->delete();
                                $record->delete();
                            });
                            Notification::make()->title('Moved to trash')->success()->send();
                        }),
                    
                    RestoreAction::make()
                        ->action(function ($record) {
                            DB::transaction(function () use ($record) {
                                Account_ledger::withTrashed()->where('reference_no', $record->reference_no)->restore();
                                $record->restore();
                            });
                            Notification::make()->title('Restored')->success()->send();
                        }),

                    ForceDeleteAction::make()
                        ->action(function ($record) {
                            DB::transaction(function () use ($record) {
                                Account_ledger::withTrashed()->where('reference_no', $record->reference_no)->forceDelete();
                                $record->forceDelete();
                            });
                            Notification::make()->title('Permanently deleted')->danger()->send();
                        }),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // --- Bulk Status Changes ---
                    BulkAction::make('bulk_confirm')
                        ->label('Confirm Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            DB::transaction(function () use ($records) {
                                foreach ($records as $record) {
                                    $record->update(['status' => 'Confirmed']);
                                    Account_ledger::where('reference_no', $record->reference_no)->update(['status' => 'Confirmed']);
                                }
                            });
                            Notification::make()->title('Selected Confirmed')->success()->send();
                        }),

                    // --- Default Synced Bulk Actions ---
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            DB::transaction(function () use ($records) {
                                foreach ($records as $record) {
                                    Account_ledger::where('reference_no', $record->reference_no)->delete();
                                    $record->delete();
                                }
                            });
                        }),
                    
                    RestoreBulkAction::make()
                        ->action(function (Collection $records) {
                            DB::transaction(function () use ($records) {
                                foreach ($records as $record) {
                                    Account_ledger::withTrashed()->where('reference_no', $record->reference_no)->restore();
                                    $record->restore();
                                }
                            });
                        }),

                    ForceDeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            DB::transaction(function () use ($records) {
                                foreach ($records as $record) {
                                    Account_ledger::withTrashed()->where('reference_no', $record->reference_no)->forceDelete();
                                    $record->forceDelete();
                                }
                            });
                        }),
                ]),
            ]);
    }
}