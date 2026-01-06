<?php

namespace App\Filament\Resources\MoneyTransfers\Schemas;

use App\Models\MoneyTransfer;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MoneyTransferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uid'),
                TextEntry::make('branch_id'),
                TextEntry::make('user_id'),
                TextEntry::make('reference_no'),
                TextEntry::make('reference'),
                TextEntry::make('account_from'),
                TextEntry::make('amount_from')
                    ->numeric(),
                TextEntry::make('currency_from'),
                TextEntry::make('account_to'),
                TextEntry::make('amount_to')
                    ->numeric(),
                TextEntry::make('currency_to'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('comission')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('date_confirm')
                    ->date(),
                TextEntry::make('date_update')
                    ->date(),
                TextEntry::make('update_by')
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (MoneyTransfer $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
