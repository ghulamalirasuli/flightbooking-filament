<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Expense;
use App\Models\Account_ledger;
use App\Models\Branch;
use App\Models\CashBox;
use App\Models\Expense_type;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

        // Remove the heading completely (To Remove the Create Expense label in header and instead I used secton)
    public function getHeading(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return ''; // Empty string removes it
        // OR return 'Expense form'; // Custom text
    }
    
    // Alternatively, hide the entire header section
    protected function getHeaderActions(): array
    {
        return [];
    }

     protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            
            $branch_code = Branch::where('id',  $data['branch_id'] )->value('branch_code');

            // Generate ONE shared Reference Number for the whole batch
            $batchReferenceNo = 'EXF'.$branch_code . now()->format('ymdHis') . strtoupper(Str::random(6));
            
            // Common IDs
            $userId = Auth::id();

                // Adding $index ensures uniqueness if processing happens in the same second
                $uniqueUid = 'EX' . now()->format('ymdHis') . rand(10, 99); 
                $uniqueRef = 'XR-' . now()->format('ymdHis') . rand(1000, 9999);

                                    // 1. Fetch the Expense_type record to get the associated service_id
            $expenseTypeRecord = Expense_type::where('name', $data['expense_type'])
                                 ->where('branch_id', $data['branch_id'])
                                 ->first();


                // C. Prepare Data Array for Transaction Table
                $ExpData = [
                    'uid'              => $uniqueUid,
                    'branch_id'        => $data['branch_id'],
                    'user_id'          => $userId,
                    'service_id'       => $expenseTypeRecord->service_id ?? null,
                    'expensetype'      => $data['expense_type'] ?? null,
                    'currency'         => $data['currency'] ?? null,
                    'reference_no'     => $batchReferenceNo, // Shared Batch ID
                    'reference'        => $uniqueRef,        // Unique ID
                    'description'      => $data['description'] ?? null,
                    'credit'           => $data['entry_type'] === 'Credit' ? $data['amount'] : 0 ,
                    'debit'            => $data['entry_type'] === 'Debit' ? $data['amount'] : 0 ,
                    'date_confirm'     => now(),
                    'date_update'      => now(),
                    'status'          => 'Pending',
                    'entry_type'      => $data['entry_type'] ?? null,
                ];

                // D. Create The Transaction Record
                $record = static::getModel()::create($ExpData);
       
   // ✅ RETURN THE RECORD - This was missing!
        return $record;

        });
    }


protected function getRedirectUrl(): string
{
    // 4. Redirect the CURRENT tab back to the List page (or wherever you prefer)
    // If you don't do this, the current tab will also stay on the View page or go to a blank form.
    return $this->getResource()::getUrl('index');
}

}
