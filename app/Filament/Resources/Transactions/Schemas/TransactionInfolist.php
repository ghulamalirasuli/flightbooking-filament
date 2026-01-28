<?php

namespace App\Filament\Resources\Transactions\Schemas;


use Filament\Infolists\Components\TextEntry;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry; // Note the namespace change


use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;


use App\Models\ContactInfo;
use App\Models\Account_ledger;
use App\Models\Income_expense;
use App\Models\Currency;
use App\Models\Service;
use App\Models\AddTransaction; 

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Overview')
                    ->icon(Heroicon::InformationCircle)
                    ->columns(12)
                    ->schema([
                        // TextEntry::make('reference_no')
                        //     ->label('Batch Ref')
                        //     ->weight(FontWeight::Bold),
                        // TextEntry::make('service.title')
                        //     ->label('Service Type'),
                        // TextEntry::make('date_confirm')
                        //     ->label('Confirmed At')
                        //     ->dateTime(),
                    ])->columnSpanFull(),

            ]);
    }
}