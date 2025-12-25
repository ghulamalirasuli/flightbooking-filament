<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Enums\IconSize;
use Filament\Infolists\Components\ImageEntry;

use Filament\Tables\Columns\ToggleColumn;

use Filament\Schemas\Schema;
use App\Models\Account_category;
use App\Models\Currency;
use App\Models\Service;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextEntry::make('branch_name'),
               
                // TextEntry::make('branch_code'),

                TextEntry::make('branch_name')
                ->label('Branch (Code)')
                ->formatStateUsing(fn ($record): string => "{$record->branch_name} ({$record->branch_code})"),

                TextEntry::make('timezone'),
                TextEntry::make('service_name'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('mobile_number')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('whatsapp')
                    ->numeric()
                    ->placeholder('-'),

                ImageEntry::make('logo')->disk('public') // MUST match BranchForm.php
                ->visibility('public')
                ->circular()
                ->defaultImageUrl(url('25.png')),



                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('about_us')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('website')
                    ->placeholder('-'),
                
                    
              IconEntry::make('status')
                    ->boolean(),

            TextEntry::make('active_accounts')
                    ->label('Account Categories')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        // Fetch names directly from the Account_category table based on IDs
                        return Account_category::whereIn('id', $record->active_accounts ?? [])
                            ->pluck('accounts_category') // Use the exact column name from Account_category
                            ->toArray();
                    }),

                
               TextEntry::make('active_currencies')
                    ->label('Currencies')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        // Fetch names directly from the Currency table based on IDs
                        return Currency::whereIn('id', $record->active_currencies ?? [])
                            ->pluck('currency_name') // Use the exact column name from Currency
                            ->toArray();
                    }),


                TextEntry::make('active_services')
                    ->label('Services')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        // Fetch names directly from the Currency table based on IDs
                        return Service::whereIn('id', $record->active_services ?? [])
                            ->pluck('title') // Use the exact column name from Service
                            ->toArray();
                    }),
             
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Branch $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
