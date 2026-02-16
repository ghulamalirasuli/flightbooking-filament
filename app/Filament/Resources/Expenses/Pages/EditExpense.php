<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\EditRecord;
// --- Added missing imports ---
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Account_ledger;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    /**
     * This hook runs BEFORE the form is filled with data.
     * It maps the database values (credit/debit) back to the virtual 'amount' field.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Check entry_type to determine which column holds the amount
        $data['amount'] = ($data['entry_type'] === 'Credit') ? $data['credit'] : $data['debit'];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            
            $userId = Auth::id(); // Defined here to be used in both updates

            // 1. Update main transaction record
            $record->update([
                'expense_id'  => $data['expense_id'],
                'account'     => $data['account'] ?? null,
                'currency'    => $data['currency'] ?? null,
                'description' => $data['description'] ?? null,
                'credit'      => $data['entry_type'] === 'Credit' ? $data['amount'] : 0,
                'debit'       => $data['entry_type'] === 'Debit' ? $data['amount'] : 0,
                'entry_type'  => $data['entry_type'] ?? null,
                'date_update' => now(),
                'update_by'   => $userId,
            ]);

            // 2. Update linked Ledger entry (flipping Debit/Credit logic)
            Account_ledger::where('uid', $record->uid)
                ->update([
                    'account'      => $data['account'], 
                    'description'  => $data['description'],
                    'credit'       => $data['entry_type'] === 'Debit' ? $data['amount'] : 0, 
                    'debit'        => $data['entry_type'] === 'Credit' ? $data['amount'] : 0,
                    'currency'     => $data['currency'],
                    'user_id'      => $userId,
                    'branch_id'    => $data['branch_id'],
                    'date_update'  => now(),
                ]);

            return $record;
        });
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
{
    // 4. Redirect the CURRENT tab back to the List page (or wherever you prefer)
    // If you don't do this, the current tab will also stay on the View page or go to a blank form.
    return $this->getResource()::getUrl('index');
}

}