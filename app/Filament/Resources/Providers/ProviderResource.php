<?php

namespace App\Filament\Resources\Providers;

use App\Filament\Resources\Providers\Pages\ManageProviders;

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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Accounts;
use App\Models\Provider;
use BackedEnum;
use UnitEnum;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;
    protected static ?int $navigationSort = 8;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Management';

    protected static ?string $recordTitleAttribute = 'account_uid';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_uid')
                    ->label('Supplier')
                    ->options(function () {
                        return Accounts::query()
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($account) {
                                // Use the accessor here - gives you "Ahmad B2B (KabuL=l)"
                                return [$account->uid => $account->account_name_with_category_and_branch];
                            });
                    })
                    ->searchable()
                    ->required(),
            
                TextInput::make('auth_type')
                    ->required()
                    ->default('normal'),
                TextInput::make('api_key'),
                TextInput::make('api_secret'),
                TextInput::make('base_url')
                    ->url()
                    ->required(),
                Textarea::make('auth_endpoint')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('url_endpoint')
                    ->required(),
                TextInput::make('extra_config'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uid'),
                 TextColumn::make('account_uid')
                ->label('Supplier')
                ->formatStateUsing(fn (Provider $record) => 
                    $record->account?->account_name_with_category_and_branch ?? $record->account_uid
                )
                ->searchable(),
                TextEntry::make('auth_type'),
                TextEntry::make('api_key')
                    ->placeholder('-'),
                TextEntry::make('api_secret')
                    ->placeholder('-'),
                TextEntry::make('base_url'),
                TextEntry::make('auth_endpoint')
                    ->columnSpanFull(),
                TextEntry::make('url_endpoint'),
                TextEntry::make('status')
                    ->numeric(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Provider $record): bool => $record->trashed()),
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
            ->recordTitleAttribute('account_uid')
            ->columns([
                TextColumn::make('uid')
                    ->searchable(),
                TextColumn::make('account_uid')
                    ->searchable(),
                TextColumn::make('auth_type')
                    ->searchable(),
                TextColumn::make('api_key')
                    ->searchable(),
                TextColumn::make('api_secret')
                    ->searchable(),
                TextColumn::make('base_url')
                    ->searchable(),
                TextColumn::make('url_endpoint')
                    ->searchable(),
                TextColumn::make('status')
                    ->numeric()
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
            'index' => ManageProviders::route('/'),
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
