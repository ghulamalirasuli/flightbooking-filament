<?php 

namespace App\Filament\Resources\AccountLedgers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
// Unified v4 namespaces (as used in your AccountsTable)
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
// Form components
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

use Illuminate\Database\Eloquent\Builder;

class AccountLedgersRelationManager extends RelationManager
{
    protected static string $relationship = 'accountLedgers'; 

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_no')
            ->columns([
                TextColumn::make('date_confirm')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable(),
                TextColumn::make('currencyInfo.currency_name') 
                    ->label('Currency')
                    ->badge(),
                TextColumn::make('credit')
                    ->numeric()
                    ->color('success'),
                TextColumn::make('debit')
                    ->numeric()
                    ->color('danger'),
                TextColumn::make('description')
                    ->limit(50),
            ])
            ->filters([
    SelectFilter::make('currency')
        ->relationship('currencyInfo', 'currency_name', function (Builder $query) {
            // 1. Get the IDs from the parent Account's access_currency array
            $authCurrencies = $this->getOwnerRecord()->access_currency ?? [];

            // 2. Filter the dropdown list to only show these IDs
            return $query->whereIn('id', $authCurrencies);
        })
        ->searchable()
        ->preload(),
])
            ->headerActions([
                CreateAction::make()
                    ->label('New Entry')
                    ->form([
                        DatePicker::make('date_confirm')
                            ->required()
                            ->default(now()),
                        TextInput::make('credit')
                            ->numeric()
                            ->default(0),
                        TextInput::make('debit')
                            ->numeric()
                            ->default(0),
                        Textarea::make('description'),
                    ]),
            ])
            ->actions([
                // FIX: Add ViewAction and the Menu Icon
                ActionGroup::make([
                    ViewAction::make(), 

                    EditAction::make()
                        ->form([
                            DatePicker::make('date_confirm')->required(),
                            TextInput::make('credit')->numeric(),
                            TextInput::make('debit')->numeric(),
                            Textarea::make('description'),
                        ]),

                    DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical') // This creates the 3-dot menu
                ->tooltip('Actions'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}