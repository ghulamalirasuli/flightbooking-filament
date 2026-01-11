<?php
namespace App\Filament\Resources\MoneyTransfers\Schemas;

use App\Models\MoneyTransfer;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Enums\FontWeight;

class MoneyTransferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transfer Status')
                    ->schema([
                        Grid::make(12)->schema([
                            TextEntry::make('reference_no')
                                ->label('Ref #')
                                ->weight(FontWeight::Bold)
                                ->icon('heroicon-m-hashtag')->columnSpan(4),

                            TextEntry::make('amount')
                                    ->numeric()
                                    ->suffix(fn ($record) => " " . ($record->mtcurrency->currency_code ?? ''))->columnSpan(4),

                            TextEntry::make('status')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'Completed' => 'success',
                                    'Pending' => 'warning',
                                    default => 'gray',
                                })->columnSpan(4),
                        ]),
                    ])->columnSpanFull(),


                Section::make('Transfer Details')
                    ->schema([
                        Grid::make(12)->schema([
                                  Section::make('Source (From)')
                                    ->icon('heroicon-m-arrow-up-circle')
                                    ->columnSpan(3)
                                    ->schema([
                                        // This now fetches the NAME from the Branch model
                                        TextEntry::make('branch.branch_name')
                                            ->label('Sending Branch'),

                                        // This calls your custom accessor for the full name
                                        TextEntry::make('accountFrom.account_name_with_category_and_branch')
                                            ->label('Account Details')
                                            ->weight(FontWeight::Bold)
                                            ->color('primary'),

                                    ])->columnSpan(6),


                              Section::make('Destination (To)')
                        ->icon('heroicon-m-arrow-down-circle')
                        ->columnSpan(3)
                        ->schema([
                            // This fetches the destination branch name
                            TextEntry::make('destBranch.branch_name')
                                ->label('Receiving Branch'),

                            // This calls the full account detail for the receiver
                            TextEntry::make('accountTo.account_name_with_category_and_branch')
                                ->label('Account Details')
                                ->weight(FontWeight::Bold)
                                ->color('success'),
                        ])->columnSpan(6),
                        ]),
                    ])->columnSpanFull(),



             

                   
               Section::make('Additional Information')
                    ->schema([
                        Grid::make(12)->schema([
                            TextEntry::make('comission')->label('Comission')->color('danger')->placeholder('---')->columnSpan(4),
                            TextEntry::make('date_confirm')->label('Confirmed')->date()->columnSpan(4),
                            TextEntry::make('user.name')->label('Created By')->columnSpan(4),
                        ]),
                        TextEntry::make('description')->markdown()->columnSpanFull(),
                    ])->columnSpanFull(),

            ]);
    }
}