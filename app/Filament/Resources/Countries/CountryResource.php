<?php

namespace App\Filament\Resources\Countries;

use App\Filament\Resources\Countries\Pages\ManageCountries;
use App\Models\Country;
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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

     protected static ?int $navigationSort = 1;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | UnitEnum | null $navigationGroup = 'Flight Setting';


    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('iso')->helperText('Countries Two Letter name ex: AF'),
                TextInput::make('name')
                    ->required()->helperText('Countries full Uppercase name ex: AFGHANISTAN'),
                TextInput::make('nicename')->required()->helperText('Countries full Capitalized name ex: Afghanistan'),
                TextInput::make('iso3')->helperText('Countries Three Letter name ex: AFN'),
                TextInput::make('numcode'),
                TextInput::make('phonecode')->helperText('Countries phone code ex: 93')
                    ->tel(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('iso')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('slug')
                    ->placeholder('-'),
                TextEntry::make('nicename')
                    ->placeholder('-'),
                TextEntry::make('iso3')
                    ->placeholder('-'),
                TextEntry::make('numcode')
                    ->placeholder('-'),
                TextEntry::make('phonecode')
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Country $record): bool => $record->trashed()),
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
                TextColumn::make('iso')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nicename')
                    ->searchable(),
                TextColumn::make('iso3')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('numcode')
                    ->searchable(),
                TextColumn::make('phonecode')
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
            'index' => ManageCountries::route('/'),
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
