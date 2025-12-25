<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAccounts extends EditRecord
{
    protected static string $resource = AccountsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    protected function getSavedNotification(): ?Notification
{
    return Notification::make()
        ->success()
        ->title('Account updated')
        ->body('The Account has been updated successfully.');
}
 protected function getRedirectUrl():string
    {
        return $this->getResource()::getUrl('index');
    }
}
