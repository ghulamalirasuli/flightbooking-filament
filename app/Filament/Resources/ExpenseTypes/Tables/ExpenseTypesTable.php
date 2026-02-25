<?php

namespace App\Filament\Resources\ExpenseTypes\Tables;

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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;

use Filament\Notifications\Notification;

use App\Models\Service;
use App\Models\Expense_type;
use App\Models\Branch;

class ExpenseTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),

                TextColumn::make('name')
                ->label('Expense Type')
                ->state(fn ($record) => "{$record->name}  ({$record->servicetype?->title})")
                ->searchable(['name'])
                ->sortable(['name']),

                 ToggleColumn::make('is_active') // Replace 'status' with your actual boolean field name
                ->label('Status')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->name} is now Active."
                        : "{$record->name} has been set to Inactive.";

                    // Dispatch the custom notification
                    Notification::make()
                        ->title($message)
                        ->success() // Sets the notification color to green
                        ->duration(5000) // Optional: show for 5 seconds
                        ->send();
                }),
            ])
            ->filters([
                  SelectFilter::make('service_id')
                            ->label('Expense Category')
                            ->options(Service::where('status', true)->where('is_income', false)->pluck('title', 'id'))
                            ->searchable()
                            ->columnSpan(3),

                TrashedFilter::make(),
            ])
            ->recordActions([
                    ActionGroup::make([
                        ViewAction::make(),
                        EditAction::make(),
                        DeleteAction::make(),
                        ForceDeleteAction::make()->label('Delete forever'),
                        RestoreAction::make(),
                    ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
