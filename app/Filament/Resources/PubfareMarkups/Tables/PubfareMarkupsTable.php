<?php

namespace App\Filament\Resources\PubfareMarkups\Tables;

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

use Filament\Notifications\Notification;

class PubfareMarkupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.account_name')
                    ->label('Supplier')
                    // This replaces the "Ahmad" with the "Ahmad - Category (Branch)" version
                    ->formatStateUsing(fn ($record) => $record->supplier?->account_name_with_category_and_branch ?? 'N/A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currencyInfo.currency_name')->label('Currency')->searchable(), // worked

                TextColumn::make('fare_type')->label('Fare Type')->searchable(),
                TextColumn::make('fare_to')->label('Fare To')->searchable(),
                ToggleColumn::make('status')
                    ->label('Status')
                    // 1. Convert the string from DB to a boolean for the toggle display
                    ->state(fn ($record): bool => $record->status === 'Confirmed') 
                    
                    // 2. Map the toggle's boolean back to your strings when clicked
                    ->updateStateUsing(function ($record, bool $state) {
                        $newStatus = $state ? 'Confirmed' : 'Pending';
                        
                        $record->update([
                            'status' => $newStatus,
                        ]);

                        // 3. Determine the notification message
                        $message = $newStatus === 'Confirmed' 
                            ? "Markup is now Confirmed." 
                            : "Markup is now Pending.";

                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                            
                        return $newStatus;
                    })
                    ->onColor('success') // Green for Confirmed
                    ->offColor('warning')// Yellow/Orange for Pending
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                  ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
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
