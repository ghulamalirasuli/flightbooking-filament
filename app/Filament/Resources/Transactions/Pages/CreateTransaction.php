<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// Import your Models
use App\Models\ContactInfo;
use App\Models\Account_ledger;
use App\Models\Income_expense;
use App\Models\Currency;
use App\Models\Service;
use App\Models\AddTransaction; // Ensure this is imported to use static::getModel() safely or use the class directly

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // ---------------------------------------------------------
            // 1. EXTRACT DATA & PREPARE COMMON VARIABLES
            // ---------------------------------------------------------
            $documents = $data['Document'] ?? []; 
            
            // Common Contact Data
            $contactData = [
                'fullname'      => $data['contact_name'] ?? null,
                'mobile_number' => $data['mobile_number'] ?? null,
                'email'         => $data['email'] ?? null,
            ];

            // Generate ONE shared Reference Number for the whole batch
            $batchReferenceNo = 'TRF' . now()->format('ymdHis') . strtoupper(Str::random(9));
            
            // Common IDs
            $userId = Auth::id();
            $defaultCurrency = Currency::where('defaults', true)->value('id');
            $serviceContent = Service::where('id', $data['service'] ?? null)->value('content');

            $createdRecords = [];

            // ---------------------------------------------------------
            // 2. LOOP THROUGH DOCUMENTS (Create Transaction PER Item)
            // ---------------------------------------------------------
            foreach ($documents as $index => $doc) {
                
                // A. Prepare Unique IDs for this specific row
                // Adding $index ensures uniqueness if processing happens in the same second
                $uniqueUid = 'TRX' . now()->format('ymdHis') . rand(10, 99) . $index; 
                $uniqueRef = 'R-' . now()->format('ymdHis') . rand(1000, 9999);

                $fixedPrice = $doc['fixed_price'] ?? 0;
                $soldPrice  = $doc['sold_price'] ?? 0;
                // $profit     = $soldPrice - $fixedPrice;
            

                $fcur = Currency::where('id',  $doc['from_currency'] )->value('buy_rate');
                $tcur = Currency::where('id',  $doc['to_currency'] )->value('sell_rate');

                $f_price = $fixedPrice /  $fcur;
                $s_price = $soldPrice / $tcur;
                $profit = $s_price - $f_price;


                // B. Calculate Prices for this specific item
               

                // C. Prepare Data Array for Transaction Table
                $trxData = [
                    'uid'              => $uniqueUid,
                    'reference_no'     => $batchReferenceNo, // Shared Batch ID
                    'reference'        => $uniqueRef,        // Unique ID
                    'branch_id'        => $data['branch_id'],
                    'to_branch'        => $data['to_branch'] ?? null,
                    'user_id'          => $userId,
                    
                    // Accounts
                    'account_from'     => $data['from_account'] ?? null,
                    'account_to'       => $data['to_account'] ?? null,
                    
                    // Service & Currency
                    'service_type'     => $data['service'] ?? null,
                    'service_content'  => $serviceContent,
                    'from_currency'    => $doc['from_currency'] ?? null,
                    'to_currency'      => $doc['to_currency'] ?? null,
                    'default_currency' => $defaultCurrency,

                    // Financials
                    'fixed_price'      => $fixedPrice,
                    'sold_price'       => $soldPrice,
                    'profit'           => $profit,

                    // Document Details
                    'description'      => $doc['description'] ?? null,
                    'fullname'         => $doc['fullname'] ?? ($contactData['fullname'] ?? null),
                    'doc_type'         => $doc['doctype'] ?? null,
                    'doc_number'       => $doc['doc_number'] ?? null,

                    // Dates & Status
                    'depart_date'      => $data['depart_date'] ?? null,
                    'arrival_date'     => $data['arrival_date'] ?? null,
                    'delivery_date'    => $data['delivery_date'] ?? null,
                    'status'           => 'Pending',
                    'pay_status'       => 'Unpaid',
                    'date_confirm'     => now(),
                    'date_update'      => now(),
                ];

                // D. Create The Transaction Record
                $record = static::getModel()::create($trxData);
                $createdRecords[] = $record;

                // ---------------------------------------------------------
                // 3. CREATE LEDGERS (Linked to specific Record UID)
                // ---------------------------------------------------------
                
                // Ledger: FROM Account (Income/Receivable logic)
                Account_ledger::create([
                    'uid'           => $record->uid, 
                    'account'       => $record->account_from, 
                    'reference_no'  => $record->reference_no,
                    'reference'     => $record->reference,
                    'description'   => $doc['description'] ?? 'Transaction Entry',
                    'credit'        => $fixedPrice, 
                    'debit'         => 0,
                    'currency'      => $doc['from_currency'],
                    'status'        => 'Pending',
                    'user_id'       => $userId,
                    'branch_id'     => $record->branch_id,
                    'date_confirm'  => now(),
                    'service_id'    => $record->service_type, 
                ]);

                // Ledger: TO Account
                Account_ledger::create([
                    'uid'           => $record->uid, 
                    'account'       => $record->account_to, 
                    'reference_no'  => $record->reference_no,
                    'reference'     => $record->reference,
                    'description'   => $doc['description'] ?? 'Transaction Entry',
                    'credit'        => 0,
                    'debit'         => $soldPrice,
                    'currency'      => $doc['to_currency'],
                    'status'        => 'Pending',
                    'user_id'       => $userId,
                    'branch_id'     => $record->branch_id,
                    'date_confirm'  => now(),
                    'service_id'    => $record->service_type, 
                ]);

                // Ledger: Income Expense (Cost/Profit Tracking)
                if ($fixedPrice > 0) {
                    Income_expense::create([
                        'uid'           => $record->uid,
                        'user_id'       => $userId,
                        'branch_id'     => $record->branch_id,
                        'type'          => 'Income', 
                        'service_uid'   => $record->service_type, 
                        'reference_no'  => $record->reference_no,
                        'reference'     => $record->reference,
                        'description'   => 'Cost for: ' . ($doc['fullname'] ?? 'Service'),
                        'credit'        => $profit,
                        'debit'         => 0, 
                        'currency'      => $defaultCurrency, 
                        'date_confirm'  => now(),
                        'date_update'   => now(),
                    ]);
                }
            }

            // ---------------------------------------------------------
            // 4. CREATE CONTACT INFO (ONCE)
            // ---------------------------------------------------------
            // We use the first record to establish the foreign key link (uid),
            // but the 'reference_no' ties it to the whole batch.
            $mainRecord = $createdRecords[0] ?? null;

            if ($mainRecord && !empty($contactData['fullname'])) {
                ContactInfo::create([
                    'uid'           => $mainRecord->uid, // Links strictly to the first transaction row
                    'reference_no'  => $batchReferenceNo, // Links broadly to all transactions in this batch
                    'branch_id'     => $mainRecord->branch_id,
                    'user_id'       => $userId,
                    'fullname'      => $contactData['fullname'],
                    'mobile_number' => $contactData['mobile_number'],
                    'email'         => $contactData['email'],
                ]);
            }

            // Return the first record so Filament knows the creation was successful
            // and has a model to redirect to.
            return $mainRecord;
        });
    }

    // App\Filament\Resources\Transactions\Pages\CreateTransaction.php

protected function afterCreate(): void
{
    // 1. Get the record that was just created
    $record = $this->getRecord();

    // 2. Generate the URL for the View page
    // Note: Since we changed the binding to reference_no, 
    // this will automatically use the reference_no in the URL.
    $url = $this->getResource()::getUrl('view', ['record' => $record]);

    // 3. Execute JavaScript to open the URL in a new tab
    $this->js("window.open('{$url}', '_blank')");
}

protected function getRedirectUrl(): string
{
    // 4. Redirect the CURRENT tab back to the List page (or wherever you prefer)
    // If you don't do this, the current tab will also stay on the View page or go to a blank form.
    return $this->getResource()::getUrl('index');
}

/*
protected static function calculateProfit(callable $get, callable $set): void
{
    $fixedPrice = (float) $get('fixed_price');
    $soldPrice = (float) $get('sold_price');
    $fromCurrencyId = $get('from_currency');
    $toCurrencyId = $get('to_currency');

    if ($fixedPrice && $soldPrice && $fromCurrencyId && $toCurrencyId) {
        $fcur = Currency::find($fromCurrencyId)?->buy_rate;
        $tcur = Currency::find($toCurrencyId)?->sell_rate;

        if ($fcur && $tcur && $fcur > 0 && $tcur > 0) {
            $profit = ($soldPrice / $tcur) - ($fixedPrice / $fcur);
            $set('profit', round($profit, 2));
        }
    }
}
*/
}