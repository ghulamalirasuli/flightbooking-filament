<?php

namespace App\Filament\Resources\AccountLedgers\Pages;

use App\Filament\Resources\AccountLedgers\AccountLedgerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountLedger extends ViewRecord
{
    protected static string $resource = AccountLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
