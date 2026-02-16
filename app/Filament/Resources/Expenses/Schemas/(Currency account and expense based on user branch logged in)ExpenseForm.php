<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use App\Models\Accounts;
use App\Models\Expense_type;

use Filament\Support\Icons\Heroicon;
class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make('Expense form') // white card background
                ->icon(Heroicon::ArrowTurnRightDown)
                ->iconColor('warning')
                ->extraAttributes([
                    // This targets the top border to match Filament's Warning color
                    'style' => 'border-top: 4px solid rgb(245, 158, 11);' 
                ])  
                 ->schema([

                Grid::make(12)->schema([

                Select::make('entry_type')
                ->options([
                    'Debit' => 'Debit',
                    'Credit' => 'Credit',
                ])->default('Debit')->columnSpan(3),
                
                Select::make('expense_id')
                    ->label('Expense Type')
                    ->options(function () {
                        $user = auth()->user();
                        
                        if (!$user || !$user->branch) {
                            return [];
                        }
                        
                        return Expense_type::query()
                            ->where('is_active', true)
                            ->where('branch_id', $user->branch->id)
                            ->pluck('type', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->columnSpan(3),
                    
                Select::make('eccount')
                        ->label('Account')
                        ->options(function () {
                         $user = auth()->user();
                            if (!$user || !$user->branch) {
                                 return [];
                            }
                            return Accounts::query()
                                    ->with(['accountType', 'branch']) // Eager load for performance
                                    ->where('is_active', true)
                                    ->where('branch_id', $user->branch->id) // If branch_id in expense_type references 
                                    ->get()
                                    ->mapWithKeys(function ($account) {
                                        $name = $account->account_name;
                                        $category = $account->accountType?->accounts_category ?? 'N/A';
                                        $branch = $account->branch?->branch_name ?? 'N/A';

                                        return [
                                                 $account->uid => "({$branch}) {$name} - {$category}",
                                                ];
                                            });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(6),
                ])->columnSpanFull(),

            Grid::make(12)->schema([
                Select::make('currency')
            ->label('Currency')
            ->options(function () {
                $user = auth()->user();
                
                if (!$user || !$user->branch) {
                    return [];
                }
                
                // Get the active currency IDs from the user's branch
                $activeCurrencyIds = $user->branch->active_currencies ?? [];
                
                if (empty($activeCurrencyIds)) {
                    return [];
                }
                
                // Fetch currencies where ID is in the active_currencies array
                return \App\Models\Currency::query()
                    ->whereIn('id', $activeCurrencyIds)
                    ->where('status', true) // Optional: only show active currencies
                    ->pluck('currency_name', 'id') // Adjust 'currency_name' to your actual column name
                    ->toArray();
            })
            ->searchable()
            ->required()
            ->columnSpan(4),

             TextInput::make('debit')
                    ->required()
                    ->default(0)
                    ->numeric()->columnSpan(4),

            TextInput::make('credit')
                    ->required()
                    ->default(0)
                    ->numeric()->columnSpan(4),
               

            ])->columnSpanFull(),

                Textarea::make('description')->columnSpanFull(),

                 ])->columnSpanFull(),
            ]);
    }
}
