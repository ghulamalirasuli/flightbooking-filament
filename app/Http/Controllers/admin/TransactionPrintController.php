<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AddTransaction;
use App\Models\Account_ledger;
use App\Models\User;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Accounts;
use App\Models\Comments;

class TransactionPrintController extends Controller
{

    public function print_from(Request $request, $reference_no, $account_id, $currency_id)
{
    // Fetch the SPECIFIC account from URL parameter - NOT from transaction
    $account = Accounts::with(['accountType', 'branch', 'currency'])
        ->where('uid', $account_id)
        ->firstOrFail();
        
    $currency = Currency::findOrFail($currency_id);

    // Eager load the reference record for shared info (branch, etc.)
    $record = AddTransaction::with(['branch', 'user'])
        ->where('reference_no', $reference_no)
        ->firstOrFail();

    $activeBranch = $record->branch ?? auth()->user()->branch;
    $settings = $this->getSettings($activeBranch);

    // Get transactions where this account is SENDER (From) - uses fixed_price
    $transactionsFrom = AddTransaction::where([
        'reference_no'  => $reference_no,
        'from_currency' => $currency_id,
        'account_from'  => $account_id,
    ])
    ->with(['user', 'currencyFrom', 'currencyTo', 'service'])
    ->orderBy('date_update', 'DESC')
    ->get()
    ->map(function($t) {
        $t->transaction_type = 'Outgoing';
        $t->amount = $t->fixed_price;
        $t->related_account = $t->accountTo?->account_name_with_category_and_branch ?? 'N/A';
        return $t;
    });

    // Get transactions where this account is RECEIVER (To) - uses sold_price  
    $transactionsTo = AddTransaction::where([
        'reference_no' => $reference_no,
        'to_currency'  => $currency_id,
        'account_to'   => $account_id,
    ])
    ->with(['user', 'currencyFrom', 'currencyTo', 'service'])
    ->orderBy('date_update', 'DESC')
    ->get()
    ->map(function($t) {
        $t->transaction_type = 'Incoming';
        $t->amount = $t->sold_price;
        $t->related_account = $t->accountFrom?->account_name_with_category_and_branch ?? 'N/A';
        return $t;
    });

    // Combine both types
    $transactions = $transactionsFrom->merge($transactionsTo)->sortByDesc('date_update');
    
    $totalOutgoing = $transactionsFrom->sum('fixed_price');
    $totalIncoming = $transactionsTo->sum('sold_price');

    // Ledger entries (unchanged logic but verify it matches)
    $fromAccountLedger = Account_ledger::where([
        'reference_no' => $reference_no,
        'currency'     => $currency_id,
        'account'      => $account_id,
    ])
    ->whereIn('status', ['Confirmed', 'Pending'])
    ->with('currencyInfo')
    ->orderBy('date_update', 'ASC')
    ->get();

    $totalCredit = $fromAccountLedger->sum('credit');
    $totalDebit = $fromAccountLedger->sum('debit');
    $balance = $totalCredit - $totalDebit; // Based on your ledger balance logic (credit - debit)

    $services = Service::where('status', true)->get();

    $remarks = Comments::where('reference_no', $reference_no)
        ->where('type', 'Remark')
        ->where('visibility', '!=', 'internal')
        ->get();

    return view('admin.transaction.print_from', compact(
        'settings', 
        'record',
        'account',           // SPECIFIC account from URL
        'currency',          // SPECIFIC currency from URL
        'transactions',
        'transactionsFrom',  // For separate totals if needed
        'transactionsTo',
        'totalOutgoing',
        'totalIncoming',
        'fromAccountLedger',
        'totalCredit',
        'totalDebit',
        'balance',
        'services',
        'remarks'
    ));
}

    public function print_to(Request $request, $reference_no, $account_id, $currency_id)
{
    // Eager load relationships
    $record = AddTransaction::with(['branch', 'user', 'accountFrom', 'accountTo', 'currencyFrom', 'currencyTo'])
        ->where('reference_no', $reference_no)
        ->firstOrFail();

    $activeBranch = $record->branch ?? auth()->user()->branch;
    $settings = $this->getSettings($activeBranch);

    // Get transactions for TO account (money arriving)
    $transactions = AddTransaction::where([
        'reference_no' => $reference_no,
        'to_currency'  => $currency_id,
        'account_to'   => $account_id,
    ])
    ->with(['user', 'currencyFrom', 'currencyTo', 'service'])
    ->orderBy('date_update', 'DESC')
    ->get();

    // Calculate totals using sold_price (amount received) instead of fixed_price
    $totalSoldPrice = $transactions->sum('sold_price');

    // Get ledger entries where money arrives (debit > 0)
    $toAccountLedger = Account_ledger::where([
        'reference_no' => $reference_no,
        'currency'     => $currency_id,
        'account'      => $account_id,
    ])
    ->whereIn('status', ['Confirmed', 'Pending'])
    ->with('currencyInfo')
    ->orderBy('date_update', 'ASC')
    ->get();

    // Calculate totals
    $totalDebit = $toAccountLedger->sum('debit');   // Money received
    $totalCredit = $toAccountLedger->sum('credit'); // Money sent (if any)
    $balance = $totalDebit - $totalCredit;          // Net balance

    return view('admin.transaction.print_to', compact(
        'settings', 
        'record',
        'transactions',
        'toAccountLedger',
        'totalSoldPrice',
        'totalDebit',
        'totalCredit',
        'balance'
    ));
}
    private function getSettings($activeBranch)
    {
        if (!$activeBranch) {
            $admin = User::where('is_admin', 1)->first();
            return [
                'logo'  => public_path('images/logo.png'), // Use public_path for file existence check
                'logo_url' => asset('images/logo.png'),
                'email' => $admin?->email ?? 'email@email.com',
                'name'  => $admin?->name ?? 'Head Office',
                'phone' => $admin?->mobile_number ?? '0799554258',
            ];
        }

        // Handle logo path properly
        $logoPath = null;
        if ($activeBranch->logo) {
            // Check if it's already a full URL
            if (filter_var($activeBranch->logo, FILTER_VALIDATE_URL)) {
                $logoPath = $activeBranch->logo;
            } else {
                // Check if file exists in storage
                $storagePath = storage_path('app/public/' . $activeBranch->logo);
                $publicPath = public_path('storage/' . $activeBranch->logo);
                
                if (file_exists($publicPath)) {
                    $logoPath = asset('storage/' . $activeBranch->logo);
                } elseif (file_exists($storagePath)) {
                    $logoPath = asset('storage/' . $activeBranch->logo);
                }
            }
        }

        return [
            'logo'  => $logoPath ?? asset('images/logo.png'),
            'email' => $activeBranch->email ?? $activeBranch->branch_email ?? '',
            'name'  => $activeBranch->branch_name ?? 'Branch Office',
            'phone' => $activeBranch->whatsapp ?? $activeBranch->phone ?? '',
            'address' => $activeBranch->address ?? '',
        ];
    }
}