<?php

namespace App\Filament\Resources\DocStatuses;

use App\Filament\Resources\DocStatuses\Pages\ManageDocStatuses;
use App\Models\DocStatus;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;

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

class DocStatusResource extends Resource
{
    protected static ?string $model = DocStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'docstatus';


     protected static ?string $navigationLabel = 'Document Status';

    protected static ?string $modelLabel = 'Document Status';
    
    protected static ?string $pluralModelLabel = 'Document Status';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('docstatus')
                    ->label('Document Status')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('docstatus')
            ->columns([
                TextColumn::make('docstatus')
                    ->label('Document Status')
                    ->searchable(),

                ToggleColumn::make('status') // Replace 'status' with your actual boolean field name
                ->label('Status')
                 ->onColor('success')
                ->offColor('danger')
                ->afterStateUpdated(function ($record, bool $state) {
                    // Determine the message based on the new state
                    $message = $state
                        ? "{$record->docstatus} is now Active."
                        : "{$record->docstatus} has been set to Inactive.";

                    // Dispatch the custom notification
                    Notification::make()
                        ->title($message)
                        ->success() // Sets the notification color to green
                        ->duration(5000) // Optional: show for 5 seconds
                        ->send();
                }),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
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
            'index' => ManageDocStatuses::route('/'),
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
