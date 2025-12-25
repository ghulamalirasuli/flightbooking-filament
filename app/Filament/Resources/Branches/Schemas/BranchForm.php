<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Split;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use App\Models\Account_category;
use App\Models\Currency;
use App\Models\Service;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $timezoneOptions = array_combine($timezones, $timezones);

        return $schema
            ->components([
                Section::make('Basic Information')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('branch_name')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->columnSpan(6),

                                TextInput::make('branch_code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->columnSpan(6),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('timezone')
                                    ->label('Branch Timezone')
                                    ->options($timezoneOptions)
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(6)
                                    ->helperText('Used for all date/time displays in this branch.'),

                                TextInput::make('service_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(6),
                            ]),
                    ]),

                Section::make('Contact & Online Presence')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->columnSpan(6)
                                    ->prefixIcon('heroicon-m-envelope'),

                                TextInput::make('mobile_number')
                                    ->numeric()
                                    ->prefixIcon('heroicon-m-phone')
                                    ->columnSpan(6),

                                TextInput::make('whatsapp')
                                    ->numeric()
                                    ->prefixIcon('heroicon-m-chat-bubble-left-ellipsis')
                                    ->columnSpan(6),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('website')
                                    ->url()
                                    ->prefixIcon('heroicon-m-link')
                                    ->columnSpan(6),

                                FileUpload::make('logo')
                                    ->label('Branch Logo')
                                    ->image()
                                    ->disk('public')
                                    ->directory('branch-logos')
                                    ->imageEditor()
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Square PNG/JPG, max 1 MB recommended.')
                                    ->columnSpan(6),
                            ]),
                    ]),

                Section::make('Address & Description')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Textarea::make('address')
                            ->rows(2)
                            ->maxLength(500),

                        Textarea::make('about_us')
                            ->label('About Us')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
     Section::make('Accessible Resources')
                    ->icon('heroicon-o-key')
                    ->schema([

                Select::make('active_accounts')
                    ->label('Account Categories')
                    ->options(
                        Account_category::query()
                            ->where('is_active', true)
                            ->pluck('accounts_category', 'id')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Choose the account categories this branch will use.'),

                    Select::make('active_currencies')
                    ->label('Currencies')
                    ->options(
                        Currency::query()
                            ->where('status', true)
                            ->pluck('currency_name', 'id')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Choose the currencies this branch will use.'),

                    Select::make('active_services')
                    ->label('Services')
                    ->options(
                        Service::query()
                            ->where('status', true)
                            ->pluck('title', 'id')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Choose the services this branch will use.'),
                    ]),

            ]);
    }
}