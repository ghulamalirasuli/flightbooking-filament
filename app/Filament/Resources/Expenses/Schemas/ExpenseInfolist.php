<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\Expense;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uid'),
                TextEntry::make('branch.id')
                    ->label('Branch'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('service_uid'),
                TextEntry::make('account'),
                TextEntry::make('currency'),
                TextEntry::make('reference_no')
                    ->placeholder('-'),
                TextEntry::make('reference')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('credit')
                    ->numeric(),
                TextEntry::make('debit')
                    ->numeric(),
                TextEntry::make('date_confirm')
                    ->date(),
                TextEntry::make('date_update')
                    ->date(),
                TextEntry::make('update_by')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('entry_type')
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Expense $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
