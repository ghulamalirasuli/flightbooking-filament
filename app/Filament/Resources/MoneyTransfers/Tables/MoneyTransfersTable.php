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
    // 1. FROM BRANCH
    Select::make('branch_id')
        ->label('From Branch')
        ->options(Branch::where('status', true)->pluck('branch_name', 'id'))
        ->live()
        ->afterStateUpdated(function ($set) {
            $set('account_from', null);
            $set('to_branch', null); // Reset To Branch if From changes
            $set('account_to', null);
        })
        ->searchable()
        ->required()
        ->columnSpan(6),

    // 2. TO BRANCH (Excludes the selected From Branch)
    Select::make('to_branch')
        ->label('To Branch')
        ->options(function (callable $get) {
            // $fromBranch = $get('branch_id');
            return Branch::where('status', true)
                // ->when($fromBranch, fn ($q) => $q->where('id', '!=', $fromBranch)) // Exclude Kabul if selected in From
                ->pluck('branch_name', 'id');
        })
        ->live()
        ->afterStateUpdated(fn ($set) => $set('account_to', null))
        ->searchable()
        ->required()
        ->columnSpan(6),
])->columnSpanFull(),

Grid::make(12)->schema([
    // 3. FROM ACCOUNT
    Select::make('account_from')
        ->label('From Account')
        ->live() // Added live() so Account To can "see" this selection and exclude it
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
        ->searchable()
        ->required()
        ->columnSpan(6),

    // 4. TO ACCOUNT (Filters by Branch and Excludes From Account)
    Select::make('account_to')
        ->label('To Account')
        ->options(function (callable $get) {
            $toBranchId = $get('to_branch');
            $fromAccountUid = $get('account_from'); // This is a UID string
            
            if (!$toBranchId) return [];

            return Accounts::query()
                ->with(['accountType', 'branch'])
                ->where('is_active', true)
                ->where('branch_id', $toBranchId)
                // Use '!=' to exclude the selected account and match 'uid' column
                ->when($fromAccountUid, fn($q) => $q->where('uid', '!=', $fromAccountUid)) 
                ->get()
                ->mapWithKeys(function ($account) {
                    return [$account->uid => "({$account->branch?->branch_name}) {$account->account_name} - {$account->accountType?->accounts_category}"];
                });
        })
        ->searchable()
        ->required()
        ->columnSpan(6),
])->columnSpanFull(),

Grid::make(12)->schema([
    TextInput::make('amount')
        ->numeric()
        ->required()
        ->columnSpan(3),

    Select::make('currency')
        ->label('Currency')
        ->options(Currency::where('status', true)->pluck('currency_name', 'id'))
        ->required()
        ->columnSpan(3),

    Textarea::make('description')
        ->columnSpan(6)
        ->rows(2)
])->columnSpanFull(),
                  ])
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
