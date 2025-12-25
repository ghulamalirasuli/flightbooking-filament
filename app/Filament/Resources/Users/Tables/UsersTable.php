<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

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
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\BulkAction;      // Correct namespace for Tables

use Illuminate\Database\Eloquent\Collection;

use Illuminate\Support\Facades\Storage; 

use Filament\Tables\Filters\SelectFilter;
use App\Models\Branch;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('branch.branch_name')
                ->label('Branch')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),

                ImageColumn::make('photo')
                    ->circular() // Optional: makes it a circle
                    ->defaultImageUrl(url('avatar.png')), // Fallback if DB is empty
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),

                    TextColumn::make('mobile_number')
                     ->label('Mobile No.')
                    ->searchable(),

                    ToggleColumn::make('is_active')
    ->label('Active Status')
    ->onColor('success')
    ->offColor('danger')
    ->afterStateUpdated(function ($record, bool $state) {
        $record->update([
            'is_active' => $state,
            'email_verified_at' => $state ? now() : null,
        ]);

        $message = $state
            ? "{$record->name} is now Active and email marked as verified."
            : "{$record->name} has been set to Inactive and email verification removed.";

        Notification::make()
            ->title($message)
            ->success()
            ->duration(5000)
            ->send();
    }),
                //  ToggleColumn::make('is_active') // Replace 'status' with your actual boolean field name
                // ->label('Active Status')
                //  ->onColor('success')
                // ->offColor('danger')
                // ->afterStateUpdated(function ($record, bool $state) {
                //     // Determine the message based on the new state
                //     $message = $state
                //         ? "{$record->name} is now Active."
                //         : "{$record->name} has been set to Inactive.";

                //     // Dispatch the custom notification
                //     Notification::make()
                //         ->title($message)
                //         ->success() // Sets the notification color to green
                //         ->duration(5000) // Optional: show for 5 seconds
                //         ->send();
                //         }),

                TextColumn::make('email_verified_at')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),

                   TextColumn::make('address')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(100),

                TextColumn::make('created_at')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            
             
                TextColumn::make('branch_id')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('user_id')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('two_factor_code')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('two_factor_expires_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('google2fa_secret')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                IconColumn::make('is_logged_in')
                ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                TextColumn::make('last_login_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
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
            ->recordUrl(null) //---> Disable default record URL , preventing to (Edit page)
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
        BulkAction::make('activate_users')
            ->label('Activate Selected')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $records->each(function ($user) {
                    $user->update([
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);
                });
            })
            ->after(fn () => Notification::make()
                ->title('Selected users activated and marked as verified')
                ->success()
                ->send()),

        // Deactivate Selected
        BulkAction::make('deactivate_users')
            ->label('Deactivate Selected')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $records->each(function ($user) {
                    $user->update([
                        'is_active' => false,
                        'email_verified_at' => null,
                    ]);
                });
            })
            ->after(fn () => Notification::make()
                ->title('Selected users deactivated and verification removed')
                ->warning()
                ->send()),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
