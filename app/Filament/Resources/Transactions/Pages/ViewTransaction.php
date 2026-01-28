<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
// use Filament\Infolists\Infolist; // Note the change from Schema to Infolist
use Filament\Schemas\Schema; // Use this instead of Infolists

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    // Use Schema $schema as the parameter and return type
    public function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\Transactions\Schemas\TransactionInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
