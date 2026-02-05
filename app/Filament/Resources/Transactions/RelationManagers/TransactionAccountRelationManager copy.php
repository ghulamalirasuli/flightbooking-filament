<?php 
namespace App\Filament\Resources\Transactions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Account_ledger;

class TransactionAccountRelationManager extends RelationManager
{
    protected static string $relationship = 'accountFrom';
    protected static ?string $title = 'Account Ledgers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([]) // No columns, we use custom content
            ->content(function ($livewire) {
                $record = $livewire->getOwnerRecord();
                
                // Load relationships
                $record->load([
                    'accountFrom.accountType', 
                    'accountFrom.branch', 
                    'accountFrom.currency',
                    'accountTo.accountType', 
                    'accountTo.branch', 
                    'accountTo.currency'
                ]);
                
                // Get ledgers for From Account
                $fromLedgers = Account_ledger::where('account', $record->account_from)
                    ->where('reference_no', $record->reference_no)
                    ->with(['currencyInfo'])
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Get ledgers for To Account  
                $toLedgers = Account_ledger::where('account', $record->account_to)
                    ->where('reference_no', $record->reference_no)
                    ->with(['currencyInfo'])
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                return view('filament.relations.transaction-ledgers', [
                    'fromAccount' => $record->accountFrom,
                    'toAccount' => $record->accountTo,
                    'fromLedgers' => $fromLedgers,
                    'toLedgers' => $toLedgers,
                ]);
            })
            ->paginated(false);
    }
}