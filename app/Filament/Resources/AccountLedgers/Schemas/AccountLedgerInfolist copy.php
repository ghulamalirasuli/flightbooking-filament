<?php

namespace App\Filament\Resources\AccountLedgers\Schemas;

use Filament\Schemas\Schema; 
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class AccountLedgerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Top Section: Account Details
                Section::make('Account Overview')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('account_name_with_category_and_branch')
                            ->label('Account Name')
                            ->weight('bold'),
                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('mobile_number')
                            ->label('Mobile Number')
                            ->icon('heroicon-m-phone'),
                    ]),

                // 2. Bottom Section: Transaction Table
                Section::make('Transaction History')
                    ->schema([
                        // Table Header Row
                        Grid::make(12)
                            ->schema([
                                TextEntry::make('h1')->default('Date')->columnSpan(2)->weight('bold')->label(''),
                                TextEntry::make('h2')->default('Reference')->columnSpan(2)->weight('bold')->label(''),
                                TextEntry::make('h3')->default('Description')->columnSpan(4)->weight('bold')->label(''),
                                TextEntry::make('h4')->default('Credit (+)')->columnSpan(2)->weight('bold')->alignEnd()->label(''),
                                TextEntry::make('h5')->default('Debit (-)')->columnSpan(2)->weight('bold')->alignEnd()->label(''),
                            ])
                            ->extraAttributes(['class' => 'border-b pb-2 mb-2 dark:border-gray-700']),

                        // Repeatable entries acting as Table Rows
                        RepeatableEntry::make('accountLedgers') // relationship in Accounts model
                            ->label('') 
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        TextEntry::make('date_confirm')->date()->columnSpan(2)->label(''),
                                        TextEntry::make('reference_no')->fontFamily('mono')->columnSpan(2)->label(''),
                                        TextEntry::make('description')->placeholder('No desc')->columnSpan(4)->label(''),
                                        TextEntry::make('credit')->numeric(2)->color('success')->alignEnd()->columnSpan(2)->label(''),
                                        TextEntry::make('debit')->numeric(2)->color('danger')->alignEnd()->columnSpan(2)->label(''),
                                    ]),
                            ])
                    ]),
            ]);
    }
}