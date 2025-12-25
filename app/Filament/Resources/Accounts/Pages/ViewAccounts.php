<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

use App\Models\Accounts;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ViewAccounts extends ViewRecord
{
    protected static string $resource = AccountsResource::class;

    protected function getHeaderActions(): array
    {
        return [
             Action::make('back')
                ->label('Back')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            Action::make('toggle_active')
                ->label(fn (Accounts $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                ->color(fn (Accounts $record): string => $record->is_active ? 'danger' : 'success')
                ->action(function (Accounts $record): void {
                    $record->update(['is_active' => !$record->is_active]);

                    Notification::make()
                        ->title('Account status updated')
                        ->success()
                        ->send();
                }),

            Action::make('toggle_b2c')
                ->label(fn (Accounts $record): string => $record->is_b2c ? 'Remove B2C' : 'Set as B2C')
                ->color(fn (Accounts $record): string => $record->is_b2c ? 'warning' : 'primary')
                ->action(function (Accounts $record): void {
                    $record->update(['is_b2c' => !$record->is_b2c]);

                    Notification::make()
                        ->title('B2C status updated')
                        ->success()
                        ->send();
                }),

            EditAction::make(),

        ];
    }
}
