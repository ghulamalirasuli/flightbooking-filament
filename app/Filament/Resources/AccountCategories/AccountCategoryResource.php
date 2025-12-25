<?php

namespace App\Filament\Resources\AccountCategories;

use App\Filament\Resources\AccountCategories\Pages\ManageAccountCategories;
use App\Models\Account_category;
use BackedEnum;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkAction; 
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AccountCategoryResource extends Resource
{
    protected static ?string $model = Account_category::class;

     protected static ?int $navigationSort = 3;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static string | UnitEnum | null $navigationGroup = 'CMS';


    protected static ?string $recordTitleAttribute = 'Account Category';
    protected static ?string $navigationLabel = 'Account Category';

    protected static ?string $createButtonLabel = 'New Account Category';

    protected static ?string $modelLabel = 'Account Category';
protected static ?string $pluralModelLabel = 'Account Categories';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('accounts_category')
                    ->required()
                    ->label('Account Category')
                    ->maxLength(255),
                Textarea::make('description')->maxLength(65535)->rows(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Account_category')
            ->columns([
                TextColumn::make('accounts_category')
                ->label('Account Category')
                    ->searchable(),

                ToggleColumn::make('is_active') // Replace 'status' with your actual boolean field name
                ->label('Status')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->accounts_category} is now Active."
                        : "{$record->accounts_category} has been set to Inactive.";

                    // Dispatch the custom notification
                    Notification::make()
                        ->title($message)
                        ->success() // Sets the notification color to green
                        ->duration(5000) // Optional: show for 5 seconds
                        ->send();
                }),

                 TextColumn::make('description')
                    ->toggleable(isToggledHiddenByDefault: true),

                 TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                 TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                 ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->successNotificationTitle('Category details have been updated.'),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageAccountCategories::route('/'),
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
