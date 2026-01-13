<?php

namespace App\Filament\Resources\Currencies;

use App\Filament\Resources\Currencies\Pages\ManageCurrencies;
use App\Models\Currency;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Enums\IconSize;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use UnitEnum;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?int $navigationSort = 1;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string | UnitEnum | null $navigationGroup = 'CMS';

    protected static ?string $recordTitleAttribute = 'currency_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
              
                TextInput::make('currency_name')
                    ->required(),
                TextInput::make('currency_code'),
                TextInput::make('sell_rate')->numeric(),
                TextInput::make('buy_rate')->numeric(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('currency_name'),
                TextEntry::make('currency_code')
                    ->placeholder('-'),
                TextEntry::make('sell_rate')
                    ->placeholder('-'),
                TextEntry::make('buy_rate')
                    ->placeholder('-'),
                IconEntry::make('status')
                    ->boolean(),
                IconEntry::make('defaults')
                    ->boolean(),
               IconEntry::make('web')
                    ->boolean(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Currency $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Currency')
            ->columns([
                TextColumn::make('currency_name')
                    ->searchable(),
                TextColumn::make('currency_code')
                    ->searchable(),
                TextColumn::make('sell_rate')
                    ->searchable(),
                TextColumn::make('buy_rate')
                    ->searchable(),
                
                     ToggleColumn::make('status') // Replace 'status' with your actual boolean field name
                ->label('Status')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->currency_name} is now Active."
                        : "{$record->currency_name} has been set to Inactive.";

                    // Dispatch the custom notification
                    Notification::make()
                        ->title($message)
                        ->success() // Sets the notification color to green
                        ->duration(5000) // Optional: show for 5 seconds
                        ->send();
                }),
                ToggleColumn::make('defaults')
                    ->onColor('success')
                    ->offColor('danger')

                    // Optional: Keep this to prevent accidentally unsetting the default
                    ->disabled(fn (Currency $record) => $record->defaults)

                    ->afterStateUpdated(function ($record, $state) {

                        // This closure runs AFTER the toggle has been clicked and the database change is initiated.

                        // Get the currency name for the notification message
                        $title = $record->currency_name;

                        // We only proceed if the state is TRUE (the record is being set as the new default)
                        if ($state) {
                        // 1. Unset all other currencies as default
                        Currency::where('id', '!=', $record->id)
                            ->update(['defaults' => false]);

                        // 2. You don't technically need this update if the toggle is handling the $record->defaults=true,
                        // but keeping it ensures the $record instance is fully synchronized before the notification.
                        // $record->update(['defaults' => true]);

                        // 3. Dispatch the custom success notification
                        Notification::make()
                            ->title("Default currency Updated")
                            ->body("All new flight booking transactions will use {$title} as the default currency.")
                            ->success() // Green success notification
                            ->duration(7000) // Show for 7 seconds (longer for important changes)
                            ->send();

                    } else {
                        // This 'else' block will rarely run due to the `->disabled()` closure above,
                        // but it's good practice to handle it if the logic changes.
                        Notification::make()
                            ->title('Action Blocked')
                            ->body("A default currency cannot be deactivated.")
                            ->warning()
                            ->duration(5000)
                            ->send();
                    }
                }),

                 ToggleColumn::make('web') // Replace 'status' with your actual boolean field name
                ->label('Website')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->currency_name} is now set to website."
                        : "{$record->currency_name} is not set to website.";

                    // Dispatch the custom notification
                    Notification::make()
                        ->title($message)
                        ->success() // Sets the notification color to green
                        ->duration(5000) // Optional: show for 5 seconds
                        ->send();
                }),


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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCurrencies::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
