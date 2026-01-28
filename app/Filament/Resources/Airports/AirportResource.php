<?php

namespace App\Filament\Resources\Airports;

use App\Filament\Resources\Airports\Pages\ManageAirports;
use App\Models\Airport;
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
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AirportResource extends Resource
{
    protected static ?string $model = Airport::class;

    protected static ?int $navigationSort = 2;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-hand-raised';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Setting';

    protected static ?string $recordTitleAttribute = 'name';

    
    public static function form(Schema $schema): Schema
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $timezoneOptions = array_combine($timezones, $timezones);
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name'),
                TextInput::make('cityCode'),
                TextInput::make('cityName')
                    ->required(),
                TextInput::make('countryName'),
                TextInput::make('countryCode'),
                 Select::make('timezone')
                    ->options($timezoneOptions) 
                    ->searchable(),
                TextInput::make('city'),
               
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code'),
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('slug')
                    ->placeholder('-'),
                TextEntry::make('cityCode')
                    ->placeholder('-'),
                TextEntry::make('cityName'),
                TextEntry::make('countryName')
                    ->placeholder('-'),
                TextEntry::make('countryCode')
                    ->placeholder('-'),
                TextEntry::make('continent_id')
                    ->placeholder('-'),
                TextEntry::make('timezone')
                    ->placeholder('-'),
                TextEntry::make('lat')
                    ->placeholder('-'),
                TextEntry::make('lon')
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->numeric(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Airport $record): bool => $record->trashed()),
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
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
              
                TextColumn::make('cityCode')
                    ->searchable(),
                TextColumn::make('cityName')
                    ->searchable(),
                TextColumn::make('countryName')
                    ->searchable(),
                TextColumn::make('countryCode')
                    ->searchable(),
             
                TextColumn::make('timezone')
                    ->searchable(),
                
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
            'index' => ManageAirports::route('/'),
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
