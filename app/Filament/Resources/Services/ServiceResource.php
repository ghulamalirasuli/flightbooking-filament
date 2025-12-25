<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\ManageServices;
use App\Models\Service;
use App\Models\Branch;
use BackedEnum;
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

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;

use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Enums\IconSize;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\ToggleColumn;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?int $navigationSort = 2;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static string | UnitEnum | null $navigationGroup = 'CMS';

    protected static ?string $recordTitleAttribute = 'Service';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            
                // Select::make('branch_id')
                //     ->label('Branch')
                //     ->relationship('branch', 'uid') // Keep 'id' as the second argument to ensure the ID is stored
                //     ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->branch_name} ({$record->branch_code})")
                //     ->searchable(['branch_name', 'branch_code']) // Allows searching by both name and code
                //     ->preload()
                //     ->required(),
                
                    // TextInput::make('title')
                    // ->required()
                    // ->rules(function ($get) {
                    //     return [
                    //         'required',
                    //         function ($attribute, $value, $fail) use ($get) {
                    //             $branchId = $get('branch_id');
                    //             $branchCode = \App\Models\Branch::where('uid', $branchId)->value('branch_code') ?? 'default';
                    //             $slug = \Illuminate\Support\Str::slug($value . '-' . $branchCode);
                                
                    //             $exists = \App\Models\Service::where('slug', $slug)->exists();
                                
                    //             if ($exists) {
                    //                 $fail('A service with this title already exists for the selected branch.');
                    //             }
                    //         },
                    //     ];
                    // }),

                    TextInput::make('title')
                                    ->label('Service Title')
                                    ->columnSpanFull(),

                      RichEditor::make('content')->columnSpanFull(),
                 ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
             
                TextEntry::make('title'),
                
                TextEntry::make('content')
                    ->html(), // This renders the <strong> and <ul> tags instead of showing them

                IconEntry::make('status')
                    ->boolean(),

               IconEntry::make('defaults')
                    ->boolean(),

                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Service $record): bool => $record->trashed()),
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
            ->recordTitleAttribute('Service')
            ->columns([
             
                TextColumn::make('title')
                    ->searchable(),
                    // In ServiceResource.php inside the table() method
                TextColumn::make('content')
                    ->html() // This renders the <strong> and <ul> tags instead of showing them
                    ->lineClamp(2) // Optional: keeps the table neat by showing only 2 lines
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

               ToggleColumn::make('status') // Replace 'status' with your actual boolean field name
                ->label('Status')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->title} is now Active."
                        : "{$record->title} has been set to Inactive.";

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
                    ->disabled(fn (Service $record) => $record->defaults)

                    ->afterStateUpdated(function ($record, $state) {

                        // This closure runs AFTER the toggle has been clicked and the database change is initiated.

                        // Get the currency name for the notification message
                        $title = $record->title;

                        // We only proceed if the state is TRUE (the record is being set as the new default)
                        if ($state) {
                        // 1. Unset all other currencies as default
                        Service::where('id', '!=', $record->id)
                            ->update(['defaults' => false]);

                        // 2. You don't technically need this update if the toggle is handling the $record->defaults=true,
                        // but keeping it ensures the $record instance is fully synchronized before the notification.
                        // $record->update(['defaults' => true]);

                        // 3. Dispatch the custom success notification
                        Notification::make()
                            ->title("Default Service Updated")
                            ->body("All new flight booking transactions will use {$title} as the default service.")
                            ->success() // Green success notification
                            ->duration(7000) // Show for 7 seconds (longer for important changes)
                            ->send();

                    } else {
                        // This 'else' block will rarely run due to the `->disabled()` closure above,
                        // but it's good practice to handle it if the logic changes.
                        Notification::make()
                            ->title('Action Blocked')
                            ->body("A default service cannot be deactivated.")
                            ->warning()
                            ->duration(5000)
                            ->send();
                    }
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
                BulkAction::make('activate_service')
                    ->label('Activate Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($service) {
                            $service->update([
                                'status' => true,
                            ]);
                        });
                    })
                    ->after(fn () => Notification::make()
                        ->title('Selected services activated ')
                        ->success()
                        ->send()),

                // Deactivate Selected
                BulkAction::make('deactivate_service')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($service) {
                            $service->update([
                                'status' => false,
                            ]);
                        });
                    })
                    ->after(fn () => Notification::make()
                        ->title('Selected services deactivated ')
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
            'index' => ManageServices::route('/'),
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
