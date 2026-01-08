<?php

namespace App\Filament\Resources\MoneyTransfers\Tables;

use Filament\Actions\CreateAction;
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


use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Group;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use App\Models\Branch;
use App\Models\Currency;
use App\Models\Accounts;


class MoneyTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('index')
            ->label('#')
            ->rowIndex(),

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
            ->headerActions([/////////////////// HEADER ACTIONS-------------
                Action::make('transfer')
            ->label('New Transafer')
            ->icon('heroicon-o-arrows-right-left')
            ->color('info')
            ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
            //Start Form----------------------FORM--------------------------------
            ->form([
                Section::make()
                    ->schema([
                      // --- ROW 1: BRANCH SELECTION ---
                                Grid::make(12)->schema([
                                    Select::make('branch_id')
                                        ->label('From Branch')
                                        ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
                                        ->live()
                                        ->afterStateUpdated(function ($set) {
                                            $set('account_from', null);
                                            $set('currency_from', null);
                                            // Reset TO side if branches conflict
                                            $set('to_branch', null); 
                                        })
                                        ->searchable()
                                        ->columnSpan(6),

                                    Select::make('to_branch')
                                        ->label('To Branch')
                                        ->options(function (callable $get) {
                                            $fromBranch = $get('branch_id');
                                            return Branch::where('status', true)
                                                ->when($fromBranch, fn ($q) => $q->where('id', '!=', $fromBranch))
                                                ->pluck('branch_name', 'id');
                                        })
                                        ->live()
                                        ->afterStateUpdated(function ($set) {
                                            $set('account_to', null);
                                            $set('currency_to', null);
                                        })
                                        ->searchable()
                                        ->columnSpan(6),
                                ])->columnSpanFull(),

                                // --- ROW 2: FROM ACCOUNT & CURRENCY ---
                                Grid::make(12)->schema([
                                    Select::make('account_from')
                                        ->label('From Account')
                                        ->options(function (callable $get) {
                                            $branchId = $get('branch_id');
                                            if (!$branchId) return [];

                                            return Accounts::query()
                                                ->with(['accountType', 'branch'])
                                                ->where('is_active', true)
                                                ->where('branch_id', $branchId)
                                                ->get()
                                                ->mapWithKeys(function ($account) {
                                                    return [$account->uid => "({$account->branch?->branch_name}) {$account->account_name} - {$account->accountType?->accounts_category}"];
                                                });
                                        })
                                        ->live()
                                        ->afterStateUpdated(fn ($set) => $set('currency_from', null))
                                        ->searchable()
                                        ->columnSpan(6),

                                    Select::make('currency_from')
                                        ->label('From Currency')
                                        ->options(function (callable $get) {
                                            $accountUid = $get('account_from');
                                            if (!$accountUid) return [];

                                            $account = Accounts::where('uid', $accountUid)->first();
                                            if (!$account || empty($account->access_currency)) return [];

                                            return Currency::whereIn('id', $account->access_currency)
                                                ->where('status', true)
                                                ->pluck('currency_name', 'id');
                                        })
                                        ->searchable()
                                        ->columnSpan(3),

                                    TextInput::make('amount_from')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(3),
                                ])->columnSpanFull(),

                                // --- ROW 3: TO ACCOUNT & CURRENCY ---
                                Grid::make(12)->schema([
                                    Select::make('account_to')
                                        ->label('To Account')
                                        ->options(function (callable $get) {
                                            $toBranchId = $get('to_branch'); // Use TO branch ID here
                                            if (!$toBranchId) return [];

                                            return Accounts::query()
                                                ->with(['accountType', 'branch'])
                                                ->where('is_active', true)
                                                ->where('branch_id', $toBranchId)
                                                ->get()
                                                ->mapWithKeys(function ($account) {
                                                    return [$account->uid => "({$account->branch?->branch_name}) {$account->account_name} - {$account->accountType?->accounts_category}"];
                                                });
                                        })
                                        ->live()
                                        ->afterStateUpdated(fn ($set) => $set('currency_to', null))
                                        ->searchable()
                                        ->columnSpan(6),

                                    Select::make('currency_to')
                                        ->label('To Currency')
                                        ->options(function (callable $get) {
                                            $toAccountUid = $get('account_to');
                                            if (!$toAccountUid) return [];

                                            $account = Accounts::where('uid', $toAccountUid)->first();
                                            if (!$account || empty($account->access_currency)) return [];

                                            return Currency::whereIn('id', $account->access_currency)
                                                ->where('status', true)
                                                ->pluck('currency_name', 'id');
                                        })
                                        ->searchable()
                                        ->columnSpan(3),

                                    TextInput::make('amount_to')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(3),
                                ])->columnSpanFull(),

                                Textarea::make('description')
                                    ->rows(3)
                                    ->columnSpanFull(),                    ])
            ])
            // 1. Hide the default primary button
    ->modalSubmitAction(false) 
    
    // 2. Define both buttons in the footer to control order
    ->extraModalFooterActions(fn (Action $action): array => [
        // This button will now be in the "corner" (leftmost)
        $action->makeModalSubmitAction('saveAndNew', arguments: ['another' => true])
            ->label('Save & New')
            ->color('gray'),
            
        // This button will be in the "middle" (replaces the old Submit)
        $action->makeModalSubmitAction('save')
            ->label('Save')
            ->color('warning'), // 'warning' matches the yellow color in your screenshot
    ])
      ->action(function (array $data, Action $action, $form, array $arguments): void {
        DB::transaction(function () use ($data) {

            // Logic (Insert) content
                         });
                        Notification::make()->title('Deposit Saved')->success()->send();
                        // Now these variables will work
                if ($arguments['another'] ?? false) {
                            // Tip: We keep the branch_id so the user doesn't have to re-select it
                            $form->fill(['branch_id' => $data['branch_id']]); 
                            $action->halt(); 
                        }
                    })
            // End Form
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
