<?php

namespace App\Filament\Resources\GroupBookings\Schemas;

use App\Models\Accounts;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Airlines;
use App\Models\Airport;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GroupBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Group Booking Details')
                    ->icon(Heroicon::GlobeAlt)
                    ->iconColor('primary')
                    ->extraAttributes([
                        'style' => 'border-top: 4px solid rgb(59, 130, 246);' 
                    ])
                    ->schema([
                        // ROW 1: CORE DATA (Branch, Account, Currency, Type)
                        Grid::make(12)
                            ->schema([
                                Select::make('branch_id')
                                    ->label('Branch')
                                    ->options(Branch::whereNotNull('branch_name')->pluck('branch_name', 'id'))
                                    ->live()
                                    ->afterStateUpdated(fn ($set) => $set('account_id', null))
                                    ->searchable()
                                    ->columnSpan(4),

                                Select::make('account_id')
                                ->label('Account')
                                ->options(function (callable $get) {
                                    $branchId = $get('branch_id');

                                    return Accounts::query()
                                        ->with(['accountType', 'branch']) // Eager load for performance
                                        ->where('is_active', true)
                                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                        ->get()
                                        ->mapWithKeys(function ($account) {
                                            // Formatting the label to match the professional Transaction Form style
                                            $name = $account->account_name ?? 'Unnamed';
                                            $category = $account->accountType?->accounts_category ?? 'N/A';
                                            $branch = $account->branch?->branch_name ?? 'N/A';

                                            return [
                                                // $account->id => "({$branch}) {$name} - {$category}",
                                                $account->uid => "({$branch}) {$name} - {$category}",
                                            ];
                                        });
                                })
                                ->live()
                                ->afterStateUpdated(fn ($set) => $set('currency', null))
                                ->required()
                                ->searchable()
                                ->columnSpan(4),

                                Select::make('currency')
                                    ->label('Currency')
                                    ->options(function ($get) {
                                        // $account = Accounts::find($get('account_id'));
                                        $account = Accounts::where('uid', $get('account_id'))->first();
                                        if (!$account || !is_array($account->access_currency)) return [];
                                        return Currency::whereIn('id', $account->access_currency)->pluck('currency_name', 'id');
                                    })
                                    ->required()
                                    ->columnSpan(2),

                                Select::make('type')
                                    ->label('Flight Type')
                                    ->options(['Direct' => 'Direct', 'Transit' => 'Transit'])
                                    ->required()
                                    ->columnSpan(2),
                            ]),

                        // ROW 2: PASSENGER PRICING GRID
                        Section::make('Passenger Pricing')
                            ->compact()
                            ->description('Enter pricing for each passenger category')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        // Adult Group
                                     Group::make([
                                    TextInput::make('adult_seat')
                                        ->label('Adult Seats')
                                        ->numeric()
                                        ->default(1),

                                    TextInput::make('adult_basefare')
                                        ->label('Base Fare')
                                        ->numeric()
                                         ->default(0)
                                        ->live()
                                        // When this changes, calculate and set the value of adult_tprice
                                        ->afterStateUpdated(function ($get, $set) {
                                            $total = (float)$get('adult_basefare') + (float)$get('adult_tax');
                                            $set('adult_tprice', $total);
                                        }),

                                    TextInput::make('adult_tax')
                                        ->label('Tax')
                                         ->default(0)
                                        ->numeric()
                                        ->live()
                                        // When this changes, calculate and set the value of adult_tprice
                                        ->afterStateUpdated(function ($get, $set) {
                                            $total = (float)$get('adult_basefare') + (float)$get('adult_tax');
                                            $set('adult_tprice', $total);
                                        }),

                                    TextInput::make('adult_tprice')
                                        ->label('Total Adult')
                                        ->numeric()
                                         ->default(0)
                                        ->readOnly()
                                        // dehydrated(true) ensures the value is sent to the DB even though it is read-only
                                        ->dehydrated(true) 
                                        ->extraInputAttributes(['class' => 'font-bold text-primary-600']),
                                ])->columnSpan(4),
                                        // Child Group
                                   // Child Group
                                Group::make([
                                    TextInput::make('child_seat')
                                        ->label('Child Seats')
                                        ->numeric()
                                         ->default(0)
                                        ->default(0),

                                    TextInput::make('child_basefare')
                                        ->label('Base Fare')
                                        ->numeric()
                                         ->default(0)
                                        ->live()
                                        ->afterStateUpdated(function ($get, $set) {
                                            $total = (float)$get('child_basefare') + (float)$get('child_tax');
                                            $set('child_tprice', $total);
                                        }),

                                    TextInput::make('child_tax')
                                        ->label('Tax')
                                        ->numeric()
                                         ->default(0)
                                        ->live()
                                        ->afterStateUpdated(function ($get, $set) {
                                            $total = (float)$get('child_basefare') + (float)$get('child_tax');
                                            $set('child_tprice', $total);
                                        }),

                                    TextInput::make('child_tprice')
                                        ->label('Total Child')
                                        ->numeric()
                                         ->default(0)
                                        ->readOnly()
                                        ->dehydrated(true) // Ensures value is sent to DB
                                        ->extraInputAttributes(['class' => 'font-bold']),
                                ])->columnSpan(4),

                                // Infant Group
                                Group::make([
                                    TextInput::make('infant_seat')
                                        ->label('Infant Seats')
                                        ->numeric()
                                        ->default(0),

                                    TextInput::make('infant_basefare')
                                        ->label('Base Fare')
                                        ->numeric()
                                         ->default(0)
                                        ->live()
                                        ->afterStateUpdated(function ($get, $set) {
                                            $total = (float)$get('infant_basefare') + (float)$get('infant_tax');
                                            $set('infant_tprice', $total);
                                        }),

                                    TextInput::make('infant_tax')
                                        ->label('Tax')
                                        ->numeric()
                                         ->default(0)
                                        ->live()
                                        ->afterStateUpdated(function ($get, $set) {
                                            $total = (float)$get('infant_basefare') + (float)$get('infant_tax');
                                            $set('infant_tprice', $total);
                                        }),

                                    TextInput::make('infant_tprice')
                                        ->label('Total Infant')
                                        ->numeric()
                                         ->default(0)
                                        ->readOnly()
                                        ->dehydrated(true) // Ensures value is sent to DB
                                        ->extraInputAttributes(['class' => 'font-bold']),
                                ])->columnSpan(4),
                                    ]),
                            ]),

                        // ROW 3: FLIGHT ITINERARY REPEATER
                     Section::make('Flight Itinerary')
    ->icon(Heroicon::PaperAirplane)
    ->description('Add one or more flight segments to this booking.')
    ->schema([
        Repeater::make('groupFlights')
            ->relationship('groupFlights')
            ->collapsible()
            ->cloneable() // Allows users to quickly copy a flight for return trips
            ->itemLabel(fn ($state) => ($state['from_f'] ?? '---') . ' ➔ ' . ($state['to_f'] ?? '---'))
            ->defaultItems(1)
            ->schema([
                Grid::make(15) // 15 columns for fine-tuned horizontal alignment
                    ->schema([
                        // --- ROW 1: Flight Identity ---
                        Select::make('airlines')
                            ->options(Airlines::pluck('name', 'name'))
                            ->searchable()
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('flightno')
                            ->label('Flight Number')
                            ->placeholder('EK202')
                            ->required()
                            ->columnSpan(2),

                        Select::make('class')
                            ->options([
                                'Economy' => 'Economy',
                                'Premium Economy' => 'Premium Economy',
                                'Business' => 'Business',
                                'First Class' => 'First Class'
                            ])
                            ->required()
                            ->columnSpan(4),

                        TextInput::make('pnr')
                            ->label('PNR / Booking Ref')
                            ->placeholder('ABC123')
                            ->extraInputAttributes(['class' => 'uppercase'])
                            ->columnSpan(3),

                        // --- ROW 2: Departure Details ---
                        Select::make('from_f')
                            ->label('Origin Airport')
                            ->options(fn () => Airport::all()->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"]))
                            ->searchable()
                            ->live()
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('f_terminal')
                            ->label('From Terminal')
                            ->placeholder('T3')
                            ->columnSpan(2),

                        DateTimePicker::make('depart_time')
                            ->label('Departure Date/Time')
                            ->required()
                            ->native(false) // Better UI for date picking
                            ->columnSpan(4),

                        Select::make('refundable')->options(['Yes' => 'Yes', 'No' => 'No'])->columnSpan(3),


                        // --- ROW 3: Arrival Details ---
                        Select::make('to_f')
                            ->label('Destination Airport')
                            ->options(fn (Get $get) => Airport::where('id', '!=', $get('from_f'))
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => "{$a->code} - {$a->name}"]))
                            ->searchable()
                            ->live()
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('t_terminal')
                            ->label('To Terminal.')
                            ->placeholder('T1')
                            ->columnSpan(2),

                        DateTimePicker::make('arrival_time')
                            ->label('Arrival Date/Time')
                            ->required()
                            ->native(false)
                            ->columnSpan(4),

                        Select::make('changeable')->options(['Changeable' => 'Changeable', 'Non-changeable' => 'Non-changeable'])->columnSpan(3),
                        

                    ]),
            ]),
    ]),

                        // ROW 4: LOGISTICS & RULES
                   Section::make('Rules & Extra Info')
    ->icon(Heroicon::ShieldCheck)
    ->iconColor('success')
    ->extraAttributes(['style' => 'border-top: 4px solid rgb(34, 197, 94);'])
    ->schema([
        Grid::make(12)
            ->schema([
                TextInput::make('baggage')
                    ->label('Baggage')
                    ->columnSpan(2),
                
                TextInput::make('hand_baggage')
                    ->label('Hand Baggage')
                    ->columnSpan(2),
                
                DateTimePicker::make('update')
                    ->label('Update Date')
                    ->default(now())
                    ->columnSpan(2),
                
                // Changed from columnSpanFull() to columnSpan(6)
                Textarea::make('description')
                    ->label('Description')
                    ->columnSpan(6) 
                    ->rows(2), // Matches height better with inputs
            ]),
    ]),
                    ])->columnSpanFull(),
            ]);
    }
}