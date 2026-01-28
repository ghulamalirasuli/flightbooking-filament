<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Accounts;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\DocType;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
// Import Group and others from the Schemas namespace
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction form') // white card background
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
                                ->label('From Branch')
                                ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('from_account', null);
                                    $set('to_branch', null); // Reset To Branch if From changes
                                    $set('to_account', null);
                                    $set('service', null);
                                })
                                ->searchable()
                                ->columnSpan(6),
                            Select::make('to_branch')
                                ->label('To Branch')
                                ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('to_account', null);
                                    $set('service', null);
                                })
                                ->searchable()
                                ->columnSpan(6),

                        ])->columnSpanFull(),
                        /* Row 2:  Account(4) | Currency(4) | Service(4) */
                        Grid::make(12)
                            ->schema([
                                /* 1. Account (Now Live) */
                                Select::make('from_account')
                                    ->label('From Account')
                                    ->options(function (callable $get) {
                                        $branchId = $get('branch_id');

                                        return Accounts::query()
                                            ->with(['accountType', 'branch']) // Eager load for performance
                                            ->where('is_active', true)
                                            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
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
                                    ->afterStateUpdated(function ($set, $get) {
                                        $set('to_account', null); // Clear the "To Account" whenever "From Account" is updated
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(6),

                                /* 1. Account (Now Live) */
                                Select::make('to_account')
                                    ->label('To Account')
                                    ->options(function (callable $get) {
                                        $branchId = $get('to_branch');
                                        $fromAccount = $get('from_account');

                                        return Accounts::query()
                                            ->with(['accountType', 'branch']) // Eager load for performance
                                            ->where('is_active', true)
                                            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                            ->when($fromAccount, fn ($q) => $q->where('uid', '!=', $fromAccount)) // Exclude the "From Account"
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

                            ])
                            ->columnSpanFull(),
                        Grid::make(12)
                            ->schema([
                                Select::make('service')
                                    ->label('Service')
                                    ->options(function (callable $get) {
                                        $branchId = $get('branch_id');
                                        if (! $branchId) {
                                            return []; // Return empty if no branch is selected
                                        }
                                        $branch = Branch::where('id', $branchId)->first();

                                        if (! $branch || empty($branch->active_services)) {
                                            return [];
                                        }

                                        return Service::query()
                                            ->whereIn('id', $branch->active_services)
                                            ->where('status', true)
                                            ->pluck('title', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(6),

                                // DateTimePicker::make('date_remind')->columnSpan(2),
                                DatePicker::make('delivery_date')->columnSpan(2),
                                DatePicker::make('depart_date')->columnSpan(2),
                                DatePicker::make('arrival_date')->columnSpan(2),

                            ])
                    ])
                            ->columnSpanFull(), // Forces this Grid to act as a full-width row

                        // --------- Add more (REPEATER)-----------

                        Section::make('Document Info')
                        ->icon(Heroicon::OutlinedDocumentPlus)
                    ->iconColor('primary')
                    ->extraAttributes([
                        // This targets the top border to match Filament's Primary Blue (approx rgb 59, 130, 246)
                        'style' => 'border-top: 4px solid rgb(59, 130, 246);' 
                    ])
                            ->schema([
                                Repeater::make('Document')
                                    ->hiddenLabel() // Removes the word "Document" above the entry
                                    ->itemNumbers()
                                    ->itemLabel(fn () => 'Entry')
                                    ->addActionLabel('Add More')
                                    ->addActionAlignment(Alignment::Start)
                                    ->reorderable(false)
                                    ->defaultItems(1)
                                    ->columnSpanFull()

                                    // Logic to hide delete button for the first item
                                    ->deleteAction(
                                        fn (Action $action) => $action->hidden(
                                            fn ($get, array $arguments) => $arguments['item'] === array_key_first($get('Document') ?? [])
                                        ),
                                    )

                                    ->schema([
                                        // Use Group from Filament\Schemas\Components\Group
                                       Group::make()
                                            ->extraAttributes(function (Group $component) {
                                                $colors = [
                                                    '#f0fdf4', // Light Green
                                                    '#eff6ff', // Light Blue
                                                    '#fff7ed', // Light Orange
                                                    '#fef2f2', // Light Red
                                                    '#faf5ff', // Light Purple
                                                    '#fdf2f8', // Light Pink
                                                    '#ecfeff', // Light Cyan
                                                ];

                                                // Get the unique ID of this specific repeater row instance
                                                $id = $component->getStatePath(); 
                                                
                                                // Generate a numeric hash from the state path string (e.g., 'Document.0')
                                                $colorIndex = abs(crc32($id)) % count($colors);

                                                return [
                                                    'style' => "
                                                        background-color: {$colors[$colorIndex]} !important; 
                                                        border: 1px solid rgba(0, 0, 0, 0.05); 
                                                        border-radius: 12px; 
                                                        padding: 20px; 
                                                        margin: 10px 0;
                                                        display: block;
                                                    ",
                                                ];
                                            })
                                            ->schema([
                                                Grid::make(12)
                                                    ->schema([
                                                        TextInput::make('fullname')->required()->columnSpan(3),
                                                        Textarea::make('description')->rows(1)->columnSpan(3),
                                                        TextInput::make('fixed_price')->numeric()->required()->columnSpan(3),
                                                        TextInput::make('sold_price')->numeric()->required()->columnSpan(3),
                                                    ]),

                                                Grid::make(12)
                                                    ->schema([
                                                        TextInput::make('doc_number')->columnSpan(3),
                                                        Select::make('doctype')
                                                            ->label('Doc Type')
                                                            ->options(DocType::where('status', true)->pluck('doctype', 'id'))
                                                            ->searchable()
                                                            ->required()
                                                            ->columnSpan(3),

                                                        Select::make('from_currency')
                                                            ->label('From Currency')
                                                            ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                                                            ->searchable()
                                                            ->required()
                                                            ->columnSpan(3),

                                                        Select::make('to_currency')
                                                            ->label('To Currency')
                                                            ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
                                                            ->searchable()
                                                            ->required()
                                                            ->columnSpan(3),
                                                    ]),
                                            ]),
                                    ]),
                            ]),                 // -> close section

                        // --------- End Add more-----------
                        // ------- Contact----
                          Section::make('Contact Info') // white card background
                          ->icon(Heroicon::PhoneArrowDownLeft)
                                ->iconColor('primary')
                                ->extraAttributes([
                                    // This targets the top border to match Filament's Primary Blue (approx rgb 59, 130, 246)
                                    'style' => 'border-top: 4px solid rgb(59, 130, 246);' 
                                ])
                                ->schema([
                                    Grid::make(12)
                                        ->schema([
                                                TextInput::make('contact_name')->columnSpan(4),
                                                TextInput::make('mobile_number')->numeric()->columnSpan(4),
                                                TextInput::make('email')->email()->columnSpan(4),
                                     ]),

                                ])
                        //------ End Contact------
                    ])->columnSpanFull(), // to make form max in width
            ]);
    }
}
