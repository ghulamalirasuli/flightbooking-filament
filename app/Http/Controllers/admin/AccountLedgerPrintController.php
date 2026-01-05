<?php 
namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use App\Models\Account_ledger;
use App\Models\Accounts;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountLedgerMail;

class AccountLedgerPrintController extends Controller
{
    public function print($ownerId, Request $request)
    {
        $account = Accounts::where('uid', $ownerId)->firstOrFail();
        $filters = $request->query('filters', []);

        $fromDate = $filters['date_range']['date_confirm_from'] ?? null;
        $toDate = $filters['date_range']['date_confirm_until'] ?? null;
        $statusFilter = $filters['status']['value'] ?? 'All';

        // 1. Create a Base Query to ensure consistency
        $baseQuery = Account_ledger::where('account', $ownerId);

        if ($statusFilter !== 'All') {
            $baseQuery->where('status', $statusFilter);
        }

        if (!empty($filters['currency']['value'])) {
            $baseQuery->where('currency', $filters['currency']['value']);
        }

        // 2. Calculate Grand Total Balance (All records matching filters, ignoring Date Range)
        $grandTotalBalances = (clone $baseQuery)
            ->selectRaw('currency, SUM(credit - debit) as bal')
            ->groupBy('currency')
            ->pluck('bal', 'currency')->toArray();

        // 3. Calculate Opening Balances (Records matching filters BEFORE fromDate)
        $openingBalances = [];
        if ($fromDate) {
            $openingBalances = (clone $baseQuery)
                ->where('date_confirm', '<', $fromDate)
                ->selectRaw('currency, SUM(credit - debit) as bal')
                ->groupBy('currency')
                ->pluck('bal', 'currency')->toArray();
        }

        // 4. Get the specific records for the table (Applying Date Range)
        $recordsQuery = (clone $baseQuery)
            ->with(['currencyInfo', 'service'])
            ->orderBy('date_confirm', 'asc');

        if ($fromDate) $recordsQuery->whereDate('date_confirm', '>=', $fromDate);
        if ($toDate)   $recordsQuery->whereDate('date_confirm', '<=', $toDate);

        $records = $recordsQuery->get();

        // Check if the request is for PDF
        if ($request->query('format') === 'pdf') {
            // Generate PDF
            $pdf = Pdf::loadView('admin.account_ledger.print_ledger', [
                'records' => $records,
                'account' => $account,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'openingBalances' => $openingBalances,
                'grandTotalBalances' => $grandTotalBalances,
                'status' => $statusFilter,
                 'format'=>$request->query('format')
            ]);

            return $pdf->download('ledger_' . $account->account_name . '_' . now()->format('Ymd') . '.pdf');
        }

        // Otherwise, return the print preview
        return view('admin.account_ledger.print_ledger', [
            'records' => $records,
            'account' => $account,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'openingBalances' => $openingBalances,
            'grandTotalBalances' => $grandTotalBalances,
            'status' => $statusFilter,
             'format'=>$request->query('format')
        ]);
    }

    public function sendEmail($ownerId, Request $request)
    {
        $account = Accounts::where('uid', $ownerId)->firstOrFail();
        $filters = $request->query('filters', []);

        $fromDate = $filters['date_range']['date_confirm_from'] ?? null;
        $toDate = $filters['date_range']['date_confirm_until'] ?? null;
        $statusFilter = $filters['status']['value'] ?? 'All';

        // 1. Create a Base Query to ensure consistency
        $baseQuery = Account_ledger::where('account', $ownerId);

        if ($statusFilter !== 'All') {
            $baseQuery->where('status', $statusFilter);
        }

        if (!empty($filters['currency']['value'])) {
            $baseQuery->where('currency', $filters['currency']['value']);
        }

        // 2. Calculate Grand Total Balance (All records matching filters, ignoring Date Range)
        $grandTotalBalances = (clone $baseQuery)
            ->selectRaw('currency, SUM(credit - debit) as bal')
            ->groupBy('currency')
            ->pluck('bal', 'currency')->toArray();

        // 3. Calculate Opening Balances (Records matching filters BEFORE fromDate)
        $openingBalances = [];
        if ($fromDate) {
            $openingBalances = (clone $baseQuery)
                ->where('date_confirm', '<', $fromDate)
                ->selectRaw('currency, SUM(credit - debit) as bal')
                ->groupBy('currency')
                ->pluck('bal', 'currency')->toArray();
        }

        // 4. Get the specific records for the table (Applying Date Range)
        $recordsQuery = (clone $baseQuery)
            ->with(['currencyInfo', 'service'])
            ->orderBy('date_confirm', 'asc');

        if ($fromDate) $recordsQuery->whereDate('date_confirm', '>=', $fromDate);
        if ($toDate)   $recordsQuery->whereDate('date_confirm', '<=', $toDate);

        $records = $recordsQuery->get();

        // Generate PDF
        $pdf = Pdf::loadView('account_ledger.print_ledger', [
            'records' => $records,
            'account' => $account,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'openingBalances' => $openingBalances,
            'grandTotalBalances' => $grandTotalBalances,
            'status' => $statusFilter,
             'format'=>$request->query('format')
        ]);

        // Send email
        Mail::to($account->email)->send(new AccountLedgerMail($account, $pdf->output()));

        return back()->with('success', 'Ledger report sent to ' . $account->email);
    }
}