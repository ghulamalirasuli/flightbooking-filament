<?php

namespace App\Filament\Resources\MoneyTransfers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MoneyTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uid')
                    ->searchable(),
                TextColumn::make('branch_id')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->searchable(),
                TextColumn::make('reference_no')
                    ->searchable(),
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('account_from')
                    ->searchable(),
                TextColumn::make('amount_from')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency_from')
                    ->searchable(),
                TextColumn::make('account_to')
                    ->searchable(),
                TextColumn::make('amount_to')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency_to')
                    ->searchable(),
                TextColumn::make('comission')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('date_confirm')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_update')
                    ->date()
                    ->sortable(),
                TextColumn::make('update_by')
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
