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
                // Header Section: Reference and Status
                Section::make()
                    ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextEntry::make('reference_no')
                                        ->label('Reference Number')
                                        ->weight(FontWeight::Bold)
                                        ->copyable()
                                        ->icon('heroicon-m-hashtag'),
                                    
                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'Pending' => 'warning',
                                            'Completed' => 'success',
                                            'Cancelled' => 'danger',
                                            default => 'gray',
                                        }),
                                ]),
                    ]),

                // Main Content: From vs To
                Grid::make(2)
                    ->schema([
                        // Origin Side
                        Section::make('Source (From)')
                            ->icon('heroicon-m-arrow-up-circle')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('branch_id')
                                    ->label('Sending Branch')
                                    // Assuming a 'branch' relationship exists on MoneyTransfer
                                    ->formatStateUsing(fn ($record) => $record->branch_id), 
                                
                                TextEntry::make('accountFrom.account_name')
                                    ->label('Account Name')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),

                                TextEntry::make('amount_from')
                                    ->label('Amount Sent')
                                    ->numeric(decimalPlaces: 2)
                                    ->weight(FontWeight::Bold)
                                    ->suffix(fn ($record) => " " . $record->currency_from),
                            ]),

                        // Destination Side
                        Section::make('Destination (To)')
                            ->icon('heroicon-m-arrow-down-circle')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('to_branch')
                                    ->label('Receiving Branch'),

                                TextEntry::make('accountTo.account_name')
                                    ->label('Account Name')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),

                                TextEntry::make('amount_to')
                                    ->label('Amount Received')
                                    ->numeric(decimalPlaces: 2)
                                    ->weight(FontWeight::Bold)
                                    ->suffix(fn ($record) => " " . $record->currency_to),
                            ]),
                    ]),

                // Transaction Details & Description
                Section::make('Additional Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('comission')
                                    ->label('Commission Fee')
                                    ->money(fn ($record) => $record->currency_from)
                                    ->color('danger'),
                                
                                TextEntry::make('date_confirm')
                                    ->label('Confirmation Date')
                                    ->date('M d, Y'),

                                TextEntry::make('user.name')
                                    ->label('Created By')
                                    ->icon('heroicon-m-user'),
                            ]),
                        
                        TextEntry::make('description')
                            ->label('Notes / Description')
                            ->markdown()
                            ->columnSpanFull()
                            ->placeholder('No description provided.'),
                    ]),

                // Audit Trail Section (Collapsible)
                Section::make('System Logs')
                    ->description('Record tracking information')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Entry Date')
                                    ->dateTime(),
                                
                                TextEntry::make('updated_at')
                                    ->label('Last Modified')
                                    ->dateTime(),

                                TextEntry::make('updated_by.name')
                                    ->label('Modified By')
                                    ->placeholder('Never modified'),
                            ]),
                    ]),
            ]);
    }
}