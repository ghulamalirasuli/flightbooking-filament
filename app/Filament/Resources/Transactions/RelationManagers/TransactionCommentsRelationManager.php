<?php

namespace App\Filament\Resources\Transactions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;

use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

use App\Models\Comments;
use App\Models\Accounts;
use App\Models\AddTransaction; // Import this

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Filament\Forms\Components\Hidden;

class TransactionCommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';
    protected static ?string $title = 'Comments';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('account')
                ->label('Related Account')
                ->options(function (RelationManager $livewire): array {
                    // Get the owner record (single transaction record)
                    $transaction = $livewire->getOwnerRecord();
                    
                    if (!$transaction || !$transaction->reference_no) {
                        return [];
                    }
                    
                    // ⚠️ KEY FIX: Fetch ALL transactions with the same reference_no
                    // This gets all accounts involved in the entire batch, not just the current record
                    $allTransactions = AddTransaction::where('reference_no', $transaction->reference_no)
                        ->with(['accountFrom', 'accountTo']) // Eager load
                        ->get();
                    
                    $accounts = [];
                    
                    foreach ($allTransactions as $trans) {
                        // Add account_from if exists
                        if ($trans->accountFrom) {
                            $account = $trans->accountFrom;
                            $label = $account->account_name_with_category_and_branch ?? $account->account_name;
                            if (!$account->is_active) {
                                $label .= ' (Inactive)';
                            }
                            // Use UID as key to avoid duplicates
                            $accounts[$account->uid] = $label;
                        }
                        
                        // Add account_to if exists
                        if ($trans->accountTo) {
                            $account = $trans->accountTo;
                            // Only add if different UID (prevents duplication)
                            if (!isset($accounts[$account->uid])) {
                                $label = $account->account_name_with_category_and_branch ?? $account->account_name;
                                if (!$account->is_active) {
                                    $label .= ' (Inactive)';
                                }
                                $accounts[$account->uid] = $label;
                            }
                        }
                    }
                    
                    return $accounts;
                })
                ->disableOptionWhen(fn (string $value): bool => 
                    !Accounts::where('uid', $value)->value('is_active')
                )
                ->searchable()
                ->required()
                ->columnSpanFull()
                ->helperText('All accounts involved in this transaction batch'),

            Grid::make(2)
                ->schema([
                    Radio::make('type')
                        ->label('Type')
                        ->options([
                            'Remark' => 'Remark',
                            'Task' => 'Task',
                            'Reminder' => 'Reminder',
                        ])
                        ->default('Remark')
                        ->required()
                        ->inline(),

                    Radio::make('reminder')
                        ->label('Reminder')
                        ->options([
                            'yes' => 'Have reminder',
                            'no' => 'No reminder',
                        ])
                        ->default('no')
                        ->required()
                        ->inline(),
                ])
                ->columnSpanFull(),

            Radio::make('visibility')
                ->label('Visibility')
                ->options([
                    'internal' => 'Internal Only',
                    'account' => 'Visible to current Account',
                    'all_accounts' => 'Visible to all Accounts',
                ])
                ->default('internal')
                ->required()
                ->inline()
                ->columnSpanFull(),

            Grid::make(2)
                ->schema([
                    TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Brief description'),
                    
                    DateTimePicker::make('date_comment')
                        ->columnSpan(1),
                ])
                ->columnSpanFull(),
            
            Textarea::make('comments')
                ->label('Comment')
                ->required()
                ->rows(3)
                ->maxLength(2000)
                ->placeholder('Enter detailed comment...')
                ->columnSpanFull(),
        ]);
    }

    // ... rest of your table() method remains the same
    public function table(Table $table): Table
    {
        return $table
            ->description('Transaction comments')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('user.name')
                    ->label('Inserted')
                    ->formatStateUsing(function ($record) {
                        $user = $record->user?->name ?? 'System';
                        $date = $record->created_at?->format('M d, Y H:i') ?? '-';
                        return "{$user}<br><span style='color: #6b7280; font-size: 0.75rem;'>{$date}</span>";
                    })
                    ->html()
                    ->searchable(['user.name']),

                TextColumn::make('type')
                    ->label('Type'),

                TextColumn::make('relatedAccount.account_name_with_category_and_branch')
                    ->label('Account Name')
                    ->wrap()
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('relatedAccount', function ($q) use ($search) {
                            $q->where('account_name', 'like', "%{$search}%")
                              ->orWhereHas('accountType', fn ($q2) =>
                                  $q2->where('accounts_category', 'like', "%{$search}%")
                              )
                              ->orWhereHas('branch', fn ($q3) =>
                                  $q3->where('branch_name', 'like', "%{$search}%")
                              );
                        });
                    })
                    ->sortable(query: function ($query, string $direction) {
                        $query
                            ->leftJoin('accounts', 'comments.account', '=', 'accounts.uid')
                            ->orderBy('accounts.account_name', $direction)
                            ->select('comments.*');
                    }),
    
                TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('comments')
                    ->label('Description')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('reminder')
                    ->label('Reminder')
                    ->formatStateUsing(function ($record) {
                        $reminderKey = $record->reminder ?? 'no';
                        $reminderLabel = match ($reminderKey) {
                            'yes' => 'Have reminder',
                            'no' => 'No reminder',
                            default => $reminderKey,
                        };
                        $date = $record->date_comment ?? '-';
                        return "{$reminderLabel}<br><span style='color: #6b7280; font-size: 0.75rem;'>{$date}</span>";
                    })
                    ->html(),

                TextColumn::make('updatedBy.name')
                    ->label('Updated By')
                    ->formatStateUsing(function ($record) {
                        $updateby = $record->updatedBy?->name ?? 'System';
                        $date_update = $record->updated_at?->format('M d, Y H:i') ?? '-';
                        return "{$updateby}<br><span style='color: #6b7280; font-size: 0.75rem;'>{$date_update}</span>";
                    })
                    ->html()
                    ->searchable(['updatedBy.name'])
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('visibility')
                    ->label('Visibility')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'internal' => 'Internal Only',
                        'account' => 'Visible to current Account',
                        'all_accounts' => 'Visible to all Accounts',
                        default => $state,
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
            ->headerActions([
                CreateAction::make()
                    ->label('Add Comment')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->modalHeading('Add New Comment')
                    ->modalWidth('2xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        $transaction = $this->getOwnerRecord();
                        
                        return array_merge($data, [
                            'uid' => 'C' . now()->format('ymdhis'),
                            'reference_no' => $transaction->reference_no,
                            'branch_id' => $transaction->branch_id,
                            'user_id' => auth()->id(),
                        ]);
                    })
                    ->using(function (array $data, string $model): Model {
                        return Comments::create($data);
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->mutateFormDataUsing(function (array $data): array {
                            $data['updated_by'] = auth()->id();
                            return $data;
                        }),
                    DeleteAction::make(),
                    ForceDeleteAction::make()->label('Delete Forever'),
                    RestoreAction::make(),
                ])
            ])
            ->emptyStateHeading('No comments yet')
            ->emptyStateDescription('Add your first comment using the "Add Comment" button.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left');
    }
}