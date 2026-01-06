<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\User;
use App\Models\Account_ledger;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DepositPrintController extends Controller
{
public function print_all(Request $request)
{
    $query = CashBox::with(['branch', 'user', 'account', 'currency']);
    
    // 1. Handle Branch Security
    $currentUser = auth()->user();
    if (!$currentUser->is_admin) {
        $query->where('branch_id', $currentUser->branch_id);
    } elseif ($request->has('branch_id')) {
        $query->where('branch_id', $request->branch_id);
    }

    // 2. Apply Filters (Matching the array structure passed from Filament)
    $filters = $request->query('filters', []);

    // Date Range Filter
    if (!empty($filters['date_range'])) {
        if (!empty($filters['date_range']['date_confirm_from'])) {
            $query->whereDate('date_confirm', '>=', $filters['date_range']['date_confirm_from']);
        }
        if (!empty($filters['date_range']['date_confirm_until'])) {
            $query->whereDate('date_confirm', '<=', $filters['date_range']['date_confirm_until']);
        }
    }

    // Status Filter
    if (!empty($filters['status']['value'])) {
        $query->where('status', $filters['status']['value']);
    }

    // Entry Type Filter
    if (!empty($filters['entry_type']['value'])) {
        $query->where('entry_type', $filters['entry_type']['value']);
    }

    // Currency Filter
    // if (!empty($filters['currency_id']['value'])) {
    //     $query->where('currency_id', $filters['currency_id']['value']);
    // }
      if (!empty($filters['currency']['value'])) {
            $baseQuery->where('currency', $filters['currency']['value']);
        }


    $deposits = $query->get();

        // Get settings for the current branch
        $branch = $currentUser->is_admin && $request->has('branch_id') ? Branch::find($request->branch_id) : $currentUser->branch;
        $settings = $this->getSettings($branch);

        // Pass the deposits and settings to the view
        // 2. Check for PDF format
        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.deposit.deposit-print_all', [
                'deposits' => $deposits,
                'settings' => $settings,
                'branch' => $branch,
                'format'=>$request->query('format')
            ])->setPaper('a4', 'portrait');

            return $pdf->stream('deposits_report.pdf');
        }

        // Default: Return the normal browser print view
        return view('admin.deposit.deposit-print_all', [
            'deposits' => $deposits, 
            'settings' => $settings,
            'branch' => $branch,
            'format'=>$request->query('format')
        ]);
        // return view('admin.deposit.deposit-print_all', compact('settings', 'deposits', 'branch'));
    }
    // Simplified to accept a single Record ID
    public function print(CashBox $record)
    {
        // Load relationships for the single record
        $record->load([
            'branch', 
            'user', 
            'account.accountType', 
            'account.branch', 
            'currency'
        ]);

        // Priority: Record's Branch > User's Branch
        $activeBranch = $record->branch ?? auth()->user()->branch;

        $settings = $this->getSettings($activeBranch);
        $ledger = Account_ledger::where('reference_no',$record->reference_no)->first();

        // We pass 'record' (singular) to the view instead of 'records'
        return view('admin.deposit.deposit-print', compact('record', 'settings','ledger'));
    }



    private function getSettings($activeBranch)
    {
        if (!$activeBranch) {
            $admin = User::where('is_admin', 1)->first();
            return [
                'logo'  => asset('images/logo.png'), 
                'email' => $admin?->email ?? 'email@email.com',
                'name'  => $admin?->name ?? 'Head Office',
                'phone' => $admin?->mobile_number ?? '0799554258',
            ];
        }

        return [
            'logo'  => $activeBranch->logo ? asset('storage/' . $activeBranch->logo) : asset('images/logo.png'),
            'email' => $activeBranch->email ?? $activeBranch->branch_email,
            'name'  => $activeBranch->branch_name,
            'phone' => $activeBranch->whatsapp ?? '',
        ];
    }
}