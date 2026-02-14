<?php

namespace App\Filament\Resources\B2CPubFaremarkups\Pages;

use App\Filament\Resources\B2CPubFaremarkups\B2CPubFaremarkupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditB2CPubFaremarkup extends EditRecord
{
    protected static string $resource = B2CPubFaremarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
