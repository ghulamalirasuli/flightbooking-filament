<?php
namespace App\Filament\Resources\AccountLedgers\Schemas;

use Filament\Schemas\Schema;
// use Filament\Infolists\Components\Section;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

use Filament\Infolists\Components\TextEntry;

class AccountLedgerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Account Overview')
                    ->columns(3) // Layout into 3 columns for the header
                    ->columnSpanFull()
                    ->schema([
                       TextEntry::make('account_display_name')
                            ->label('Account')
                            ->weight('bold')
                            ->state(function ($record) {
                                $name = $record->account_name;
                                $category = $record->accountType?->accounts_category ?? 'N/A';
                                $branch = $record->branch?->branch_name ?? 'N/A';
                                
                                return "{$name} - {$category} ({$branch})";
                            }),
                        TextEntry::make('email')
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('mobile_number')
                            ->label('Phone'),
                    ]),
            ]);
    }
}