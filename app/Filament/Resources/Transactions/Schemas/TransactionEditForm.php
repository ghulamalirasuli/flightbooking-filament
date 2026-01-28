<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
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
                Section::make('Transaction Edit')
                    ->icon(Heroicon::ArrowPath)
                    ->iconColor('warning')
                    ->extraAttributes([
                        'style' => 'border-top: 4px solid rgb(245, 158, 11);'
                    ])
                    ->schema([
                        Section::make('Account Info')
                            ->icon(Heroicon::InformationCircle)
                            ->iconColor('primary')
                            ->extraAttributes([
                                'style' => 'border-top: 4px solid rgb(59, 130, 246);'
                            ])
                            ->schema([
                                Grid::make(12)->schema([
                                    // 1. BRANCH (Live to trigger updates)
                                    Select::make('branch_id')
                                        ->label('From Branch')
                                        ->relationship('branch', 'branch_name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live() // Crucial for dependent dropdowns
                                        ->columnSpan(6),

                                    // 2. TO BRANCH (Live)
                                    Select::make('to_branch')
                                        ->label('To Branch')
                                        ->relationship('branch', 'branch_name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live() // Crucial for dependent dropdowns
                                        ->columnSpan(6),
                                ]),

                                Grid::make(12)->schema([
                                    // 3. ACCOUNT FROM (Filtered by branch_id)
                               Select::make('account_from')
                                ->label('From Account')
                                ->options(function (callable $get) {
                                    $branchId = $get('branch_id');
                                    if (!$branchId) {
                                        return [];
                                    }
                                    
                                    return \App\Models\Accounts::query()
                                        ->with(['accountType', 'branch'])
                                        ->where('branch_id', $branchId)
                                        ->where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($account) {
                                            return [
                                                $account->uid => $account->account_name_with_category_and_branch
                                            ];
                                        });
                                })
                                ->searchable()
                                ->required()
                                ->columnSpan(6),


                                    // 4. ACCOUNT TO (Filtered by to_branch)
                                   Select::make('account_to')
                                    ->label('To Account')
                                    ->options(function (callable $get) {
                                        $toBranchId = $get('to_branch');
                                        if (!$toBranchId) {
                                            return [];
                                        }
                                        
                                        return \App\Models\Accounts::query()
                                            ->with(['accountType', 'branch'])
                                            ->where('branch_id', $toBranchId)
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($account) {
                                                return [
                                                    $account->uid => $account->account_name_with_category_and_branch
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(6),
                                ])->columnSpanFull(),

                                Grid::make(12)->schema([
                                    // 5. SERVICE (Options based on Branch active_services)
                                    Select::make('service_type')
                                        ->label('Service')
                                        ->options(function ($get) {
                                            $branchId = $get('branch_id');
                                            if (!$branchId) {
                                                return [];
                                            }
                                            $branch = Branch::find($branchId);
                                            if (!$branch || empty($branch->active_services)) {
                                                return [];
                                            }
                                            return Service::whereIn('id', $branch->active_services)
                                                ->pluck('title', 'id');
                                        })
                                        ->required()
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $set('service_content', Service::find($state)?->content ?? '');
                                        })
                                        ->columnSpan(6),

                                    // Hidden Service Content
                                    TextInput::make('service_content')
                                        ->hidden()
                                        ->label('Service Content')
                                        ->disabled()
                                        ->dehydrated(false),

                                    DatePicker::make('delivery_date')->label('Delivery Date')->native(false)->columnSpan(2),
                                    DatePicker::make('depart_date')->label('Departure Date')->native(false)->columnSpan(2),
                                    DatePicker::make('arrival_date')->label('Arrival Date')->native(false)->columnSpan(2),
                                ]),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Document Info')
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->iconColor('primary')
                    ->extraAttributes([
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
                        ]),

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
                                ->preload()
                                ->live() // Add Live
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    static::calculateProfit($get, $set);
                                })->columnSpan(3),

                            Select::make('to_currency')
                                ->label('To Currency')
                                ->relationship('currencyTo', 'currency_name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live() // Add Live
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    static::calculateProfit($get, $set);
                                })->columnSpan(3),

                            // Hidden Profit Field for Form State (optional, but good for calculations)
                            TextInput::make('profit')
                                ->hidden()
                                ->dehydrated(true),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function calculateProfit(callable $get, callable $set): void
    {
        $fixedPrice = (float) ($get('fixed_price') ?? 0);
        $soldPrice = (float) ($get('sold_price') ?? 0);
        $fromCurrency = $get('from_currency');
        $toCurrency = $get('to_currency');

        if ($fixedPrice > 0 && $soldPrice > 0 && $fromCurrency && $toCurrency) {
            $fcur = Currency::find($fromCurrency)?->buy_rate;
            $tcur = Currency::find($toCurrency)?->sell_rate;

            if ($fcur && $tcur) {
                $f_price = $fixedPrice / $fcur;
                $s_price = $soldPrice / $tcur;
                $profit = $s_price - $f_price;

                $set('profit', round($profit, 2));
            }
        }
    }
}