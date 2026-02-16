<?php

namespace App\Filament\Resources\IncomeLedgers\Pages;

use App\Filament\Resources\IncomeLedgers\IncomeLedgerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditIncomeLedger extends EditRecord
{
    protected static string $resource = IncomeLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
