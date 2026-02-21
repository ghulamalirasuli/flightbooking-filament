<?php

namespace App\Filament\Resources\FareMarkups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

use Filament\Schemas\Components\Utilities\Get;

use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Airlines;
use App\Models\Airport;

use Filament\Schemas\Schema;

class FareMarkupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make('Fare Markup form')
                    ->description('Choose your supplier and the way you connect to them.')
                    ->schema([
                Grid::make(12)
                    ->schema([
                        Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->options(function (callable $get) {
                                        return Accounts::query()
                                            ->with(['accountType', 'branch']) // Eager load for performance
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($account) {
                                                $name = $account->account_name;
                                                $category = $account->accountType?->accounts_category ?? 'N/A';
                                                $branch = $account->branch?->branch_name ?? 'N/A';

                                                return [
                                                    $account->uid => "({$branch}) {$name} - {$category}",
                                                ];
                                            });
                                    })
                                    ->live()
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(6),
                                 Select::make('currency')
                                            ->label('Currency')
                                            ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->columnSpan(2),

                                   Select::make('fare_type')
                                    ->label('Fare Type')
                                    ->options([
                                        'Number' => 'Number',
                                        'Percentage' => 'Percentage',
                                    ])
                                    ->required()->columnSpan(2),


                                         Select::make('fare_to')
                                    ->label('Fare To')
                                    ->options([
                                        'B2B' => 'B2B',
                                        'B2C' => 'B2C',
                                    ])
                                    ->required()->default('B2B')->columnSpan(2),

                            ])->columnSpanFull(),


                            
                    Grid::make(12)
                    ->schema([
                        /* --- FROM AIRPORT --- */
                        Select::make('from')
                            ->label('From Airport')
                            ->options(function (Get $get) {
                                return Airport::query()
                                    // ->where('status', true) // Uncomment if you have a status column
                                    ->where('id', '!=', $get('to')) // Exclude airport selected in 'to'
                                    // ->where('code', '!=', $get('to'))
                                    ->get()
                                    ->mapWithKeys(fn ($airport) => [
                                        $airport->id => "{$airport->name} ({$airport->code})"
                                    ]); // store as id => name for better performance
                                    // ->mapWithKeys(fn ($airport) => [
                                    //     // The KEY ($airport->code) is what gets stored in the database
                                    //     $airport->code => "{$airport->name} ({$airport->code})"
                                    // ]);
                            })
                            ->live() // Ensures the 'to' field updates when this changes
                            ->searchable()
                            ->columnSpan(3),

                        /* --- TO AIRPORT --- */
                        Select::make('to')
                            ->label('To Airport')
                            ->options(function (Get $get) {
                                return Airport::query()
                                    // ->where('status', true)
                                    ->where('id', '!=', $get('from'))
                                    ->get()
                                    ->mapWithKeys(fn ($airport) => [
                                        // The KEY ($airport->id) is what gets stored in the database
                                        $airport->id => "{$airport->name} ({$airport->code})"
                                    ]);
                            })
                            ->live() // Ensures the 'from' field updates when this changes
                            ->searchable()
                            ->columnSpan(3),

                        Select::make('airlines')
                            ->label('Airline')
                            ->options(Airlines::where('status', true)->pluck('name', 'id'))
                            ->searchable()
                            ->columnSpan(3),
                        TextInput::make('flightno')
                            ->label('Flight Number') 
                            ->columnSpan(3),
                    
                    ])->columnSpanFull(),

                    Section::make('Fare Markup part')
                    ->extraAttributes([
                        // This targets the top border to match Filament's Primary Blue (approx rgb 59, 130, 246)
                        'style' => 'border-top: 4px solid rgb(59, 130, 246);' 
                    ])
                    ->schema([

                    Grid::make(12)
                    ->schema([ // Adult

                     TextInput::make('from_adult_markup')
                            ->label('From Adult Markup')
                            ->default(0) 
                            ->columnSpan(4),
                    Select::make('from_adult_action')
                            ->label('From Adult Action')
                            ->default('+')
                        ->options([
                            '+' => 'Add',
                            '-' => 'Subtract',
                        ])->columnSpan(2),

                    TextInput::make('to_adult_markup')
                            ->label('To Adult Markup') 
                            ->default(0)
                            ->columnSpan(4),

                      Select::make('to_adult_action')
                            ->label('To Adult Action')
                            ->default('+')
                        ->options([
                            '+' => 'Add',
                            '-' => 'Subtract',
                        ])->columnSpan(2),

                    ])->columnSpanFull(),


                    Grid::make(12)
                    ->schema([ // CHild

                     TextInput::make('from_child_markup')
                            ->label('From Child Markup')
                            ->default(0) 
                            ->columnSpan(4),
                    Select::make('from_child_action')
                            ->label('From Child Action')
                            ->default('+')
                        ->options([
                            '+' => 'Add',
                            '-' => 'Subtract',
                        ])->columnSpan(2),

                    TextInput::make('to_child_markup')
                            ->label('To Child Markup') 
                            ->default(0)
                            ->columnSpan(4),

                      Select::make('to_child_action')
                            ->label('To Child Action')
                            ->default('+')
                        ->options([
                            '+' => 'Add',
                            '-' => 'Subtract',
                        ])->columnSpan(2),

                    ])->columnSpanFull(),



                    Grid::make(12)
                    ->schema([ // Infant

                     TextInput::make('from_infant_markup')
                            ->label('From Infant Markup')
                            ->default(0) 
                            ->columnSpan(4),
                    Select::make('from_infant_action')
                            ->label('From Infant Action')
                            ->default('+')
                        ->options([
                            '+' => 'Add',
                            '-' => 'Subtract',
                        ])->columnSpan(2),

                    TextInput::make('to_infant_markup')
                            ->label('To Infant Markup') 
                            ->default(0)
                            ->columnSpan(4),

                      Select::make('to_infant_action')
                            ->label('To Infant Action')
                            ->default('+')
                        ->options([
                            '+' => 'Add',
                            '-' => 'Subtract',
                        ])->columnSpan(2),

                    ])->columnSpanFull(),
                    ]),
                    ])->columnSpanFull(),


            ]);
    }
}
