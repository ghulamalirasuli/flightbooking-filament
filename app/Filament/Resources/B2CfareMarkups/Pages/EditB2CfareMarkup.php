<?php

namespace App\Filament\Resources\B2CfareMarkups\Pages;

use App\Filament\Resources\B2CfareMarkups\B2CfareMarkupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditB2CfareMarkup extends EditRecord
{
    protected static string $resource = B2CfareMarkupResource::class;

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
