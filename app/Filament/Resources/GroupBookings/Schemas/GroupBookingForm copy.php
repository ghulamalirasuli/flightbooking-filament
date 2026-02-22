<?php

namespace App\Filament\Resources\GroupBookings\Schemas;

use App\Models\Accounts;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Airlines;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

use Filament\Schemas\Schema;

class GroupBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->columns(3)
                    ->schema([
                        // Branch: Visible only to Admin/Superuser
                        // Select::make('branch_id')
                        //     ->label('Branch')
                        //     ->options(Branch::all()->pluck('branch_name', 'uid'))
                        //     ->searchable()
                        //     ->hidden(fn () => !in_array(auth()->user()->user_type, ['Superuser', 'Admin']))
                        //     ->default(auth()->user()->branch_id),

Select::make('branch_id')
    ->label('Branch')
    ->options(Branch::whereNotNull('branch_name')->pluck('branch_name', 'id'))
    ->live()
    ->afterStateUpdated(function ($set) {
        $set('account_id', null); // Reset account when branch changes
        $set('currency', null);   // Reset currency when branch changes
    })
    ->searchable(),

// Account Selection filtered by Branch
Select::make('account_id') // Matching the 'account_id' field in your GroupBooking model
    ->label('Account')
    ->placeholder('Select an account')
    ->options(function (callable $get) {
        $branchId = $get('branch_id');

        return Accounts::query()
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->mapWithKeys(function ($account) {
                $name = $account->account_name ?? 'Unnamed';
                $category = $account->accountType?->accounts_category ?? 'N/A';
                $branch = $account->branch?->branch_name ?? 'N/A';

                return [
                    $account->id => "({$branch}) {$name} - {$category}",
                ];
            });
    })
    ->live()
    ->afterStateUpdated(fn ($set) => $set('currency', null)) // Reset currency when account changes
    ->searchable()
    ->required(),

// Currency Selection filtered by Account's 'access_currency' array
Select::make('currency')
    ->label('Currency')
    ->placeholder(fn ($get) => $get('account_id') ? 'Select Currency' : 'Select an account first')
    ->options(function (callable $get) {
        $accountId = $get('account_id');

        if (!$accountId) {
            return [];
        }

        // 1. Find the selected account
        $account = Accounts::where('id', $accountId)->first();

        // 2. Check if the account exists and has the access_currency array
        if (!$account || !is_array($account->access_currency) || empty($account->access_currency)) {
            return [];
        }

        // 3. Return only currencies that are in the account's allowed list
        return Currency::query()
            ->whereIn('id', $account->access_currency)
            ->whereNotNull('currency_name')
            ->pluck('currency_name', 'id');
    })
    ->live()
    ->required()
    ->searchable()
    ->disabled(fn ($get) => !$get('account_id')), // Disable until account is chosen


                        Select::make('type')
                            ->label('Flight Type')
                            ->options([
                                'Direct' => 'Direct',
                                'Transit' => 'Transit',
                            ])
                            ->required(),
                    ]),

                Section::make('Flight Details (Legs)')
                    ->schema([
                        // This handles the dynamic "airlines[]", "flight_no[]" logic from your controller
                        Repeater::make('groupFlights')
                            ->relationship('groupFlights')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('airlines')
                                            ->options(Airlines::pluck('name', 'name'))
                                            ->searchable()
                                            ->required(),
                                        TextInput::make('flightno')
                                            ->label('Flight No')
                                            ->required(),
                                        Select::make('class')
                                            ->options([
                                                'Economy' => 'Economy',
                                                'Business' => 'Business',
                                                'First Class' => 'First Class',
                                            ])
                                            ->required(),
                                        TextInput::make('pnr')
                                            ->label('PNR'),
                                    ]),
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('from_f')->label('From Airport')->required(),
                                        TextInput::make('f_terminal')->label('Terminal'),
                                        DateTimePicker::make('depart_time')->required(),
                                        TextInput::make('duration')->required()->placeholder('e.g., 3h 30m'),
                                    ]),
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('to_f')->label('To Airport')->required(),
                                        TextInput::make('t_terminal')->label('To Terminal'),
                                        DateTimePicker::make('arrival_time')->required(),
                                        TextInput::make('layover')->default('0'),
                                    ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['flightno'] ?? 'Flight Leg')
                            ->collapsible()
                            ->minItems(1),
                    ]),

                Section::make('Passenger Pricing')
                    ->description('Pricing breakdown for Adults, Children, and Infants')
                    ->schema([
                        // Adult Pricing
                        Grid::make(4)->schema([
                            TextInput::make('adult_seat')->label('Adult Seats')->numeric()->default(0),
                            TextInput::make('adult_basefare')->label('Base Fare')->numeric()->live(),
                            TextInput::make('adult_tax')->label('Tax')->numeric()->live(),
                            TextInput::make('adult_tprice')
                                ->label('Total Adult')
                                ->numeric()
                                ->placeholder(fn ($get) => (float)$get('adult_basefare') + (float)$get('adult_tax'))
                                ->readOnly(),
                        ]),
                        // Child Pricing
                        Grid::make(4)->schema([
                            TextInput::make('child_seat')->label('Child Seats')->numeric()->default(0),
                            TextInput::make('child_basefare')->label('Base Fare')->numeric()->live(),
                            TextInput::make('child_tax')->label('Tax')->numeric()->live(),
                            TextInput::make('child_tprice')
                                ->label('Total Child')
                                ->numeric()
                                ->placeholder(fn ($get) => (float)$get('child_basefare') + (float)$get('child_tax'))
                                ->readOnly(),
                        ]),
                        // Infant Pricing
                        Grid::make(4)->schema([
                            TextInput::make('infant_seat')->label('Infant Seats')->numeric()->default(0),
                            TextInput::make('infant_basefare')->label('Base Fare')->numeric()->live(),
                            TextInput::make('infant_tax')->label('Tax')->numeric()->live(),
                            TextInput::make('infant_tprice')
                                ->label('Total Infant')
                                ->numeric()
                                ->placeholder(fn ($get) => (float)$get('infant_basefare') + (float)$get('infant_tax'))
                                ->readOnly(),
                        ]),
                    ]),

                Section::make('Rules & Extra Info')
                    ->columns(2)
                    ->schema([
                        TextInput::make('baggage')->label('Baggage')->required(),
                        TextInput::make('hand_baggage')->label('Hand Baggage'),
                        Select::make('changeable')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),
                        Select::make('refundable')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),
                        DateTimePicker::make('update')
                            ->label('Update Date')
                            ->default(now())
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}