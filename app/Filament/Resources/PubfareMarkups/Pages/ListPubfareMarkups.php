<?php

namespace App\Filament\Resources\PubfareMarkups\Pages;

use App\Filament\Resources\PubfareMarkups\PubfareMarkupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPubfareMarkups extends ListRecords
{
    protected static string $resource = PubfareMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
