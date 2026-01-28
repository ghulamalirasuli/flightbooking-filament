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

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // ---------------------------------------------------------
            // 1. EXTRACT DATA
            // ---------------------------------------------------------
            $documents = $data['Document'] ?? []; 
            
            // Get the first document to populate the main Transaction Header
            // We use the first item as the "primary" info for the main record
            $firstDoc = $documents[array_key_first($documents)] ?? [];

            $contactData = [
                'fullname'      => $data['contact_name'] ?? null,
                'mobile_number' => $data['mobile_number'] ?? null,
                'email'         => $data['email'] ?? null,
            ];

            // ---------------------------------------------------------
            // 2. PREPARE MAIN TRANSACTION DATA
            // ---------------------------------------------------------
            
            // Generate IDs
            $data['uid'] = 'TRX' . now()->format('ymdHis') . rand(10, 99);
            $data['reference_no'] = 'REF-' .now()->format('ymdHis'). strtoupper(Str::random(8));
            $data['reference'] = 'R-' .now()->format('ymdHis'). rand(1000, 9999);
            
            $data['user_id'] = Auth::id();
            
            // Calculate totals
            $data['fixed_price'] = collect($documents)->sum('fixed_price');
            $data['sold_price']  = collect($documents)->sum('sold_price');
            $data['profit']      = $data['sold_price'] - $data['fixed_price'];

            // $data['default_currency'] = Currency::where('defaults',true)->pluck('id');
            $data['default_currency'] = Currency::where('defaults', true)->value('id');
            $data['service_content'] = Service::where('id',$data['service'] )->value('content');

            $data['status'] = 'Pending'; 
            $data['pay_status'] = 'Unpaid';
            $data['date_confirm']  = now();
            $data['date_update']   = now();

            // --- CRITICAL FIX: Map Repeater Fields to Main Table ---
            // The table needs 'from_currency', 'doc_type', etc. We grab them from the first doc.
            if (!empty($firstDoc)) {
                $data['from_currency']  = $firstDoc['from_currency'] ?? null; // Fixes 1364 Error
                $data['to_currency']    = $firstDoc['to_currency'] ?? null;
                $data['doc_type']       = $firstDoc['doctype'] ?? null;       // Note: Form calls it 'doctype', DB calls it 'doc_type'
                $data['doc_number']     = $firstDoc['doc_number'] ?? null;
                $data['description']    = $firstDoc['description'] ?? null;
                // If the main transaction needs a fullname and it wasn't in contact info, use the doc's fullname
                if (empty($data['fullname'])) {
                    $data['fullname'] = $firstDoc['fullname'] ?? null;
                }
            }

            // --- Map Form Fields to Database Columns ---
            if (isset($data['from_account'])) $data['account_from'] = $data['from_account'];
            if (isset($data['to_account']))   $data['account_to'] = $data['to_account'];
            if (isset($data['service']))      $data['service_type'] = $data['service'];

            // ---------------------------------------------------------
            // 3. CLEANUP DATA ARRAY
            // ---------------------------------------------------------
            unset($data['Document']);
            unset($data['contact_name']);
            unset($data['mobile_number']);
            unset($data['email']);
            unset($data['from_account']);
            unset($data['to_account']);
            unset($data['service']);

            // ---------------------------------------------------------
            // 4. CREATE MAIN RECORD
            // ---------------------------------------------------------
            $record = static::getModel()::create($data);

            // ---------------------------------------------------------
            // 5. CREATE CONTACT INFO
            // ---------------------------------------------------------
            if (!empty($contactData['fullname'])) {
                ContactInfo::create([
                    'uid'           => $record->uid,
                    'reference_no'  => $record->reference_no,
                    'branch_id'     => $record->branch_id,
                    'user_id'       => Auth::id(),
                    'fullname'      => $contactData['fullname'],
                    'mobile_number' => $contactData['mobile_number'],
                    'email'         => $contactData['email'],
                    // 'slug'          => Str::slug($contactData['fullname']),
                ]);
            }

            // ---------------------------------------------------------
            // 6. PROCESS REPEATER (LEDGERS)
            // ---------------------------------------------------------
            foreach ($documents as $doc) {
                // A. Create Account Ledger (Income/Receivable)
                // From
                   Account_ledger::create([
                    'uid'           => $record->uid, 
                    'account'       => $record->account_from, 
                    'reference_no'  => $record->reference_no,
                    'reference'     => $record->reference,
                    'description'   => $doc['description'] ?? 'Transaction Entry',
                    'credit'        => $doc['fixed_price'], // Money In
                    'debit'         => 0,
                    'currency'      => $doc['from_currency'],
                    'status'        => 'Pending',
                    'user_id'       => Auth::id(),
                    'branch_id'     => $record->branch_id,
                    'date_confirm'  => now(),
                    'service_id'    => $record->service_type, 
                ]);
                // To
                Account_ledger::create([
                    'uid'           => $record->uid, 
                    'account'       => $record->account_to, 
                    'reference_no'  => $record->reference_no,
                    'reference'     => $record->reference,
                    'description'   => $doc['description'] ?? 'Transaction Entry',
                    'credit'        => 0 , // Money In
                    'debit'         => $doc['sold_price'],
                    'currency'      => $doc['to_currency'],
                    'status'        => 'Pending',
                    'user_id'       => Auth::id(),
                    'branch_id'     => $record->branch_id,
                    'date_confirm'  => now(),
                    'service_id'    => $record->service_type, 
                ]);

                // B. Create Income_expense Ledger (Cost/Expense)
                // Assuming 'fixed_price' is your cost
                if (!empty($doc['fixed_price']) && $doc['fixed_price'] > 0) {
                    Income_expense::create([
                        'uid'           => $record->uid,
                        'user_id'       => Auth::id(),
                        'branch_id'     => $record->branch_id,
                        'type'          => 'Income', // Cost of service
                        'service_uid'   => $record->service_type, // Or map to a service UID if available
                        'reference_no'  => $record->reference_no,
                        'reference'     => $record->reference,
                        'description'   => 'Cost for: ' . ($doc['fullname'] ?? 'Service'),
                        'credit'        => $record->profit,
                        'debit'         => 0, // Money Out (Cost)
                        'currency'      => Currency::where('defaults', true)->value('id'), // Assuming cost is in 'from_currency'
                        'date_confirm'  => now(),
                        'date_update'   => now(),
                    ]);
                }
            }

            return $record;
        });
    }
}