<?php

namespace App\Filament\Resources\MoneyTransfers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MoneyTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uid')
                    ->required(),
                TextInput::make('branch_id')
                    ->required(),
                TextInput::make('user_id')
                    ->required(),
                TextInput::make('reference_no')
                    ->required(),
                TextInput::make('reference')
                    ->required(),
                TextInput::make('account_from')
                    ->required(),
                TextInput::make('amount_from')
                    ->required()
                    ->numeric(),
                TextInput::make('currency_from')
                    ->required(),
                TextInput::make('account_to')
                    ->required(),
                TextInput::make('amount_to')
                    ->required()
                    ->numeric(),
                TextInput::make('currency_to')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('comission'),
                Select::make('status')
                    ->options([
            'Confirmed' => 'Confirmed',
            'Pending' => 'Pending',
            'Deleted' => 'Deleted',
            'Editted' => 'Editted',
        ])
                    ->default('Pending')
                    ->required(),
                DatePicker::make('date_confirm')
                    ->required(),
                DatePicker::make('date_update')
                    ->required(),
                TextInput::make('update_by'),
            ]);
    }
}
