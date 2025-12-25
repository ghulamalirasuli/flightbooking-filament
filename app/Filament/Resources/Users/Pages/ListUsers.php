<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;


class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

          public function getTabs(): array
{
    return [
        'all' => Tab::make('All users')->icon('heroicon-m-user-group'),
        'active' => Tab::make('Active users')->icon('heroicon-m-user-group')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
        'inactive' => Tab::make('Inactive users')->icon('heroicon-m-user-group')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
    ];
}
}
