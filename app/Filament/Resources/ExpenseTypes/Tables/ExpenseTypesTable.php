<?php

namespace App\Filament\Resources\ExpenseTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Notifications\Notification;

class ExpenseTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->searchable(),
                 ToggleColumn::make('is_active') // Replace 'status' with your actual boolean field name
                ->label('Status')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->type} is now Active."
                        : "{$record->type} has been set to Inactive.";

                    // Dispatch the custom notification
                    Notification::make()
                        ->title($message)
                        ->success() // Sets the notification color to green
                        ->duration(5000) // Optional: show for 5 seconds
                        ->send();
                }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
