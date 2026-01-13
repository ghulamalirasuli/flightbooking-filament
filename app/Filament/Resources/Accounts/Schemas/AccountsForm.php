<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Group;
use Filament\Support\Enums\Operation;
use Filament\Schemas\Schema;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Account_category;

class AccountsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //    Section::make('Account form') // white card background
                //     ->schema([
                Select::make('branch_id') // Binds to the correct foreign key column
                    ->label('Branch')
                    ->relationship('branch', 'id') // Keep 'id' as the second argument to ensure the ID is stored
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->branch_name}")
                    ->searchable(['branch_name']) // Allows searching by both name and code
                    ->searchable()
                    ->preload()
                    ->required(),
               
                TextInput::make('account_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('mobile_number')
                    ->numeric(),
                Select::make('gender')
                    ->options(['Male' => 'Male', 'Female' => 'Female']),
                TextInput::make('address'),

                FileUpload::make('photo')->image()->default('avatar.png'),

                TextInput::make('password')
                ->password()
                ->required()
                ->minLength(8)
                ->hiddenOn(Operation::Edit)
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create'),

                 TextInput::make('password_confirmation')
                ->password()
                ->required()
                ->same('password') // ✅ This is the key
                ->label('Confirm Password')
                 ->hiddenOn(Operation::Edit),
 

                

                 Select::make('access_currency')
                    ->label('Access Currencies')
                    ->options(Currency::pluck('currency_name', 'id'))
                    ->multiple()
                    ->preload()
                    ->required()
                    ->helperText('Choose the currencies this account will use.'),

                    Select::make('default_currency')
                    ->label('Default Currency')
                    ->relationship('currency', 'id') // Keep 'id' as the second argument to ensure the ID is stored
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->currency_name}")
                    ->searchable(['currency_name']) // Allows searching by both name and code
                    ->preload()
                    ->required()
                    ->helperText('Choose the default currency this account will use.'),

                   Select::make('account_type')
                    ->label('Account Type')
                    ->relationship('accountType', 'id') // Keep 'id' as the second argument to ensure the ID is stored
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->accounts_category}")
                    ->searchable(['accounts_category']) // Allows searching by both name and code
                    ->preload()
                    ->required()
                    ->helperText('Choose the account type this account will use.'),
                    //  ])->columnSpanFull(),// to make form max in width
            ]);
    }
}
