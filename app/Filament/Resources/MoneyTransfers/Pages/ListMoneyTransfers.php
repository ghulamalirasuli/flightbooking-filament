<?php

namespace App\Filament\Resources\MoneyTransfers\Pages;

use App\Filament\Resources\MoneyTransfers\MoneyTransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMoneyTransfers extends ListRecords
{
    protected static string $resource = MoneyTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
