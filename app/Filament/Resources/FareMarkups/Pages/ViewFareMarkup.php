<?php

namespace App\Filament\Resources\FareMarkups\Pages;

use App\Filament\Resources\FareMarkups\FareMarkupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewFareMarkup extends ViewRecord
{
    protected static string $resource = FareMarkupResource::class;
    // This property removes the dynamic ID and sets a fixed title
    protected ?string $heading = 'Fare Markup Details'; // change view 1 to Fare Markup Details

    protected function getHeaderActions(): array
    {
        return [
               Action::make('back')
                ->label('Back')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
            EditAction::make(),
        ];
    }
}
