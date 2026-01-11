<?php
namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use App\Models\Account_category;
use App\Models\Currency;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // --- SECTION 1: HEADER & IDENTITY ---
            Section::make('Branch Profile')
                ->description('Core identification and contact details.')
                ->schema([
                    Grid::make(3)->schema([
                        ImageEntry::make('logo')
                            ->disk('public')
                            ->circular()
                            ->grow(false)
                            ->defaultImageUrl(url('25.png')),

                        Grid::make(2)->schema([
                            TextEntry::make('branch_name')
                                ->label('Branch Name')
                                ->weight('bold')
                                ->size('lg')
                                ->formatStateUsing(fn ($record) => "{$record->branch_name} ({$record->branch_code})"),
                            
                            IconEntry::make('status')
                                ->label('System Status')
                                ->boolean(),
                            
                            TextEntry::make('timezone')
                                ->icon('heroicon-m-clock'),
                                
                            TextEntry::make('email')
                                ->icon('heroicon-m-envelope')
                                ->copyable(),
                        ])->columnSpan(2),
                    ]),
                ]),

            // --- SECTION 2: CAPABILITIES (With Header Actions) ---
            Section::make('Active Configurations')
                ->description('Management of allowed accounts, currencies, and services.')
                ->headerActions([
                    Action::make('manage_settings')
                        ->label('Update Capabilities')
                        ->icon('heroicon-m-cog-6-tooth')
                        ->color('warning')
                        ->form([
                            Select::make('active_currencies')
                                ->multiple()
                                ->label('Currencies')
                                ->options(Currency::pluck('currency_name', 'id'))
                                ->default(fn (Branch $record) => $record->active_currencies ?? []),
                            
                            Select::make('active_accounts')
                                ->multiple()
                                ->label('Account Categories')
                                ->options(Account_category::pluck('accounts_category', 'id'))
                                ->default(fn (Branch $record) => $record->active_accounts ?? []),

                            Select::make('active_services')
                                ->multiple()
                                ->label('Services')
                                ->options(Service::pluck('title', 'id'))
                                ->default(fn (Branch $record) => $record->active_services ?? []),
                        ])
                        ->action(function (Branch $record, array $data) {
                            $record->update($data);
                        })
                ])
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('active_currencies')
                            ->label('Allowed Currencies')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(fn ($record) => Currency::whereIn('id', $record->active_currencies ?? [])->pluck('currency_name')->toArray()),

                        TextEntry::make('active_accounts')
                            ->label('Active Account Types')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(fn ($record) => Account_category::whereIn('id', $record->active_accounts ?? [])->pluck('accounts_category')->toArray()),

                        TextEntry::make('active_services')
                            ->label('Enabled Services')
                            ->badge()
                            ->color('primary')
                            ->getStateUsing(fn ($record) => Service::whereIn('id', $record->active_services ?? [])->pluck('title')->toArray()),
                    ]),
                ]),

            // --- SECTION 3: LOCATION & LOGS ---
            Section::make('Additional Information')
                ->collapsed() // Keeps the UI clean by hiding less important info
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('mobile_number')->label('Mobile'),
                        TextEntry::make('whatsapp')->label('WhatsApp'),
                        TextEntry::make('website')->url(fn($state) => $state)->color('primary'),
                        
                        TextEntry::make('address')->columnSpan(2),
                        TextEntry::make('about_us')->columnSpanFull()->prose(),
                        
                        TextEntry::make('created_at')->dateTime()->color('gray'),
                        TextEntry::make('updated_at')->dateTime()->color('gray'),
                    ]),
                ]),
        ]);
    }
}