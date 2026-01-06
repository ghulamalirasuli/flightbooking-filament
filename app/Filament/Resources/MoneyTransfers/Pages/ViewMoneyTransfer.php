<?php

namespace App\Filament\Resources\MoneyTransfers\Pages;

use App\Filament\Resources\MoneyTransfers\MoneyTransferResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMoneyTransfer extends ViewRecord
{
    protected static string $resource = MoneyTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
