<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Schemas\Schema;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

use App\Models\Accounts;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\DocType;
use App\Models\Service;

class TransactionEditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                 Section::make('Transaction Edit') // white card background
                ->icon(Heroicon::ArrowPath)
                ->iconColor('warning')
                ->extraAttributes([
                    // This targets the top border to match Filament's Warning color
                    'style' => 'border-top: 4px solid rgb(245, 158, 11);' 
                ])  
                 ->schema([
                  Section::make('Account Info')
                    ->icon(Heroicon::InformationCircle)
                    ->iconColor('primary')
                    ->extraAttributes([
                        // This targets the top border to match Filament's Primary Blue (approx rgb 59, 130, 246)
                        'style' => 'border-top: 4px solid rgb(59, 130, 246);' 
                    ])
                    ->schema([

                       Grid::make(12)->schema([
                                Select::make('branch_id')
                                    ->label('Branch')
                                    ->relationship('branch', 'branch_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()->columnSpan(6),

                                Select::make('to_branch')
                                    ->label('Branch')
                                    ->relationship('branch', 'branch_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()->columnSpan(6),
                            ]),

                 

                    Grid::make(12)->schema([
                         Select::make('account_from')
                                    ->label('From Account')
                                    ->relationship('accountFrom', 'account_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()->columnSpan(6),

                             Select::make('account_to')
                                    ->label('To Account')
                                    ->relationship('accountTo', 'account_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()->columnSpan(6),

                    ])->columnSpanFull(),
                    Grid::make(12)->schema([
                         Select::make('service_type')
                                    ->label('Service')
                                    ->relationship('service', 'title')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('service_content', Service::find($state)?->content ?? '');
                                    })->columnSpan(6),
                                TextInput::make('service_content')
                                    ->hidden()
                                    ->label('Service Content')
                                    ->disabled()
                                    ->dehydrated(false),
                             
                            DatePicker::make('delivery_date')
                                    ->label('Delivery Date')
                                    ->native(false)->columnSpan(2),
                              DatePicker::make('depart_date')
                                    ->label('Departure Date')
                                    ->native(false)->columnSpan(2),

                                DatePicker::make('arrival_date')
                                    ->label('Arrival Date')
                                    ->native(false)->columnSpan(2),
                    ]),

                    ])
                    ])->columnSpanFull(),

                        Section::make('Document Info')
                        ->icon(Heroicon::OutlinedDocumentPlus)
                    ->iconColor('primary')
                    ->extraAttributes([
                        // This targets the top border to match Filament's Primary Blue (approx rgb 59, 130, 246)
                        'style' => 'border-top: 4px solid rgb(59, 130, 246);' 
                    ])
                            ->schema([

                    Grid::make(12)->schema([
                         TextInput::make('fullname')
                                    ->label('Full Name')
                                    ->required()->columnSpan(3),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(1)->columnSpan(3),

                            TextInput::make('fixed_price')
                                    ->label('Fixed Price')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        static::calculateProfit($get, $set);
                                    })->columnSpan(3),

                             TextInput::make('sold_price')
                                    ->label('Sold Price')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        static::calculateProfit($get, $set);
                                    })->columnSpan(3),
                    ])->columnSpanFull(),
                               
            Grid::make(12)->schema([
                TextInput::make('doc_number')
                        ->label('Document Number')->columnSpan(3),

                     Select::make('doc_type')
                        ->label('Doc Type')
                        ->options(DocType::where('status', true)->pluck('doctype', 'id'))
                        ->searchable()
                        ->required()->columnSpan(3),

                     Select::make('from_currency')
                                    ->label('From Currency')
                                    ->relationship('currencyFrom', 'currency_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()->columnSpan(3),

                        Select::make('to_currency')
                                    ->label('To Currency')
                                    ->relationship('currencyTo', 'currency_name')
                                    ->required()
                                    ->searchable()
                                    ->preload()->columnSpan(3),

                ])
                            ])->columnSpanFull(),
               
            ]);
    }

    protected static function calculateProfit(callable $get, callable $set): void
    {
        $fixedPrice = $get('fixed_price') ?? 0;
        $soldPrice = $get('sold_price') ?? 0;
        $fromCurrency = $get('from_currency');
        $toCurrency = $get('to_currency');

        if ($fixedPrice > 0 && $soldPrice > 0 && $fromCurrency && $toCurrency) {
            $fcur = Currency::where('id', $fromCurrency)->value('buy_rate');
            $tcur = Currency::where('id', $toCurrency)->value('sell_rate');

            if ($fcur && $tcur) {
                $f_price = $fixedPrice / $fcur;
                $s_price = $soldPrice / $tcur;
                $profit = $s_price - $f_price;
                
                $set('profit', round($profit, 2));
            }
        }
    }
}