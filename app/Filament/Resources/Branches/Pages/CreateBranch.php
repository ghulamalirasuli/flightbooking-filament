<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;
protected function getRedirectUrl():string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Branch registered')
            ->body('The Branch has been created successfully.');
    }
}
