<?php

namespace App\Filament\Resources\IncomeLedgers\Pages;

use App\Filament\Resources\IncomeLedgers\IncomeLedgerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIncomeLedger extends ViewRecord
{
    protected static string $resource = IncomeLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
