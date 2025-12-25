<?php

namespace App\Filament\Resources\AccountCategories\Pages;

use App\Filament\Resources\AccountCategories\AccountCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAccountCategories extends ManageRecords
{
    protected static string $resource = AccountCategoryResource::class;
    protected static ?string $title = 'Account Category';

public function getTitle(): string
{
    return 'Create Account Category';
}
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Account Category'),
        ];
    }
}
