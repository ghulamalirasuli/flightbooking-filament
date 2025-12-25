<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Models\Accounts;
use App\Models\Currency;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class AccountsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Overview')
                    ->icon('heroicon-o-user')
                    ->schema([
                        ImageEntry::make('photo')
                            ->disk('public')
                            ->visibility('public')
                            ->circular()
                            ->defaultImageUrl(url('avatar.png'))
                            ->columnSpanFull(),

                    TextEntry::make('account_name')
                        ->state(fn ($record) => new HtmlString(
                            '<strong class="text-sm text-gray-600 dark:text-gray-400">Account</strong><br>' .
                            "{$record->account_name} - {$record->accountType?->accounts_category} ({$record->branch?->branch_name})"
                        ))
                        ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email address')
                                    ->placeholder('-'),

                                TextEntry::make('mobile_number')
                                    ->numeric()
                                    ->placeholder('-'),

                                TextEntry::make('gender')
                                    ->badge()
                                    ->placeholder('-'),

                                TextEntry::make('address')
                                    ->placeholder('-'),

                             TextEntry::make('user.name')
                                    ->label('Added by')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Status & Access')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->boolean()
                                    ->label('Active'),

                                IconEntry::make('is_b2c')
                                    ->boolean()
                                    ->label('B2C'),

                                IconEntry::make('is_logged_in')
                                    ->boolean()
                                    ->label('Logged In'),
                            ]),

                        TextEntry::make('currency.currency_name')
                            ->label('Default Currency'),

                        TextEntry::make('access_currency')
                            ->label('Access Currencies')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(function ($record) {
                                return Currency::whereIn('id', $record->access_currency ?? [])
                                    ->pluck('currency_name')
                                    ->toArray();
                            }),
                    ]),

                Section::make('Security & Timestamps')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('email_verified_at')
                                    ->dateTime()
                                    ->placeholder('-'),

                                TextEntry::make('last_login_at')
                                    ->dateTime()
                                    ->placeholder('-'),

                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->placeholder('-'),

                                TextEntry::make('deleted_at')
                                    ->dateTime()
                                    ->visible(fn (Accounts $record): bool => $record->trashed()),

                                TextEntry::make('google2fa_secret')
                                    ->placeholder('-')
                                    ->label('2FA Secret'),
                            ]),
                    ]),
            ]);
    }
}