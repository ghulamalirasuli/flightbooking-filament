<?php

namespace App\Filament\Resources\PubfareMarkups\Pages;

use App\Filament\Resources\PubfareMarkups\PubfareMarkupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewPubfareMarkup extends ViewRecord
{
    protected static string $resource = PubfareMarkupResource::class;
     // This property removes the dynamic ID and sets a fixed title
    protected ?string $heading = 'Fare Markup Details'; // -> change View A251224073426 to Fare Markup Details

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
