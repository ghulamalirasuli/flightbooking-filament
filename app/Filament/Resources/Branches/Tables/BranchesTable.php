<?php

namespace App\Filament\Resources\Branches\Tables;

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

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;

use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;


use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage; 
use App\Models\Account_category;
use App\Models\Currency;
use App\Models\Service;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                
                // TextColumn::make('branch_name')
                //     ->searchable(),

                // TextColumn::make('branch_code')
                //     ->searchable(),
                TextColumn::make('branch_name')
                    ->label('Branch (Code)')
                    ->formatStateUsing(fn ($record): string => "{$record->branch_name} ({$record->branch_code})")
                    ->searchable(['branch_name', 'branch_code'])
                    ->sortable(),

                TextColumn::make('active_accounts')
                    ->label('Account Categories')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        // Fetch names directly from the Account_category table based on IDs
                        return Account_category::whereIn('id', $record->active_accounts ?? [])
                            ->pluck('accounts_category') // Use the exact column name from Account_category
                            ->toArray();
                    }),

                TextColumn::make('timezone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('service_name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('mobile_number')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                    
// TextColumn::make('debug_path')
//     ->label('Physical Path')
//     ->getStateUsing(fn ($record) => Storage::disk('public')->path($record->logo)),
    
            ImageColumn::make('logo')
                ->disk('public') // MUST match BranchForm.php
                ->visibility('public')
                ->circular()
                ->defaultImageUrl(url('25.png')),

                 ToggleColumn::make('status')
                    ->label('Status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->afterStateUpdated(function ($record, bool $state) {
                        $record->update([
                            'status' => $state,
                            'email_verified_at' => $state ? now() : null,
                        ]);

                        $message = $state
                            ? "{$record->branch_name} is now Active and email marked as verified."
                            : "{$record->branch_name} has been set to Inactive and email verification removed.";

                        Notification::make()
                            ->title($message)
                            ->success()
                            ->duration(5000)
                            ->send();
                    }),

                TextColumn::make('website')
                    ->toggleable(isToggledHiddenByDefault: true),

                 TextColumn::make('address')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('about_us')
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordUrl(null)
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
        BulkAction::make('activate_branch')
            ->label('Activate Selected')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $records->each(function ($user) {
                    $user->update([
                        'status' => true,
                    ]);
                });
            })
            ->after(fn () => Notification::make()
                ->title('Selected branches activated ')
                ->success()
                ->send()),

        // Deactivate Selected
        BulkAction::make('deactivate_branch')
            ->label('Deactivate Selected')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $records->each(function ($user) {
                    $user->update([
                        'status' => false,
                    ]);
                });
            })
            ->after(fn () => Notification::make()
                ->title('Selected branches deactivated ')
                ->warning()
                ->send()),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
