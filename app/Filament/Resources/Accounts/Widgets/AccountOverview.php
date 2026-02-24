<?php

namespace App\Filament\Resources\Accounts\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Accounts;


class AccountOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Accounts', Accounts::count())
                ->icon('heroicon-o-user-group'),
                
            Stat::make('Active Accounts', Accounts::where('is_active', true)->count())
                ->icon('heroicon-o-user-plus'),
                
            Stat::make('Inactive Accounts', Accounts::where('is_active', false)->count())
                ->icon('heroicon-o-user-minus'),
                
            Stat::make('B2C Accounts', Accounts::where('is_b2c', true)->count())
                ->icon('heroicon-o-users'),

            Stat::make('B2B Accounts', Accounts::where('is_b2c', false)->count())
                ->icon('heroicon-o-users'),
        ];
    }
}
