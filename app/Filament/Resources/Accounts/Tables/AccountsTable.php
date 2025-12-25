<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use Filament\Actions\RestoreAction;        
use Filament\Actions\RestoreBulkAction;    
use Filament\Actions\ForceDeleteAction;    
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\BulkAction;

use Illuminate\Database\Eloquent\Collection;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ImageColumn;

use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Account_category;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('user.name')
                ->label('User')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),

                TextColumn::make('account_name')
                ->label('Account')
                ->state(fn ($record) => "{$record->account_name} - {$record->accountType?->accounts_category} ({$record->branch?->branch_name})")
                ->searchable(['account_name', 'accountType.accounts_category', 'branch.branch_name'])
                ->sortable(['account_name']),


                 ToggleColumn::make('is_active')
                ->label('Status')
                ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    $record->update([
                        'is_active' => $state,
                        'email_verified_at' => $state ? now() : null,
                    ]);

                    $message = $state
                        ? "{$record->account_name} is now Active and email marked as verified."
                        : "{$record->account_name} has been set to Inactive and email verification removed.";

                    Notification::make()
                        ->title($message)
                        ->success()
                        ->duration(5000)
                        ->send();
                }),

                ToggleColumn::make('is_b2c')
                ->label('Is B2C')
                ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    $record->update([
                        'is_b2c' => $state,
                    ]);

                    $message = $state
                        ? "{$record->account_name} is now B2C."
                        : "{$record->account_name} is not B2C. now";


                    Notification::make()
                        ->title($message)
                        ->success()
                        ->duration(5000)
                        ->send();
                }),


                TextColumn::make('currency.currency_name')
                ->label('Default Currency')
                ->searchable(),

                TextColumn::make('access_currency')
                    ->label('Account Currencies')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        // Fetch names directly from the Currency table based on IDs
                        return Currency::whereIn('id', $record->access_currency ?? [])
                            ->pluck('currency_name') // Use the exact column name from Currency
                            ->toArray();
                    })->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),

                TextColumn::make('accountType.accounts_category')
                ->label('Account Category')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),

                TextColumn::make('branch.branch_name')
                ->label('Branch')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),
                

                TextColumn::make('mobile_number')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gender')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),
                TextColumn::make('address')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                ImageColumn::make('photo')
                    ->circular() // Optional: makes it a circle
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->defaultImageUrl(url('avatar.png')), // Fallback if DB is empty

                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
               
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

               
                
                TextColumn::make('google2fa_secret')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                IconColumn::make('is_logged_in')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                TextColumn::make('last_login_at')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('branch_id')
                ->label('Branch')
                ->options(Branch::pluck('branch_name', 'id'))
                ->searchable()
                ->preload(),
            ])
            ->recordActions([
               ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make()->label('Delete forever'),
                    RestoreAction::make(),
                ])
            ])
            ->toolbarActions([
               BulkActionGroup::make([

               // Activate Selected
        BulkAction::make('activate_account')
            ->label('Activate Selected')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $records->each(function ($user) {
                    $user->update([
                        'is_active' => true,
                    ]);
                });
            })
            ->after(fn () => Notification::make()
                ->title('Selected accounts activated ')
                ->success()
                ->send()),

        // Deactivate Selected
        BulkAction::make('deactivate_account')
            ->label('Deactivate Selected')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $records->each(function ($user) {
                    $user->update([
                        'is_active' => false,
                    ]);
                });
            })
            ->after(fn () => Notification::make()
                ->title('Selected accounts deactivated ')
                ->warning()
                ->send()),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
