<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\Account_ledger;
use App\Models\User;
use Illuminate\Http\Request;

class DepositPrintController extends Controller
{
    // Function 1: Handle Printing
    public function print($ids)
    {
        $idArray = explode(',', $ids);
        
        $records = CashBox::with([
            'branch', 
            'user', 
            'account.accountType', 
            'account.branch', 
            'currency'
        ])->whereIn('id', $idArray)->get();

        $activeBranch = $records->first()?->branch ?? auth()->user()->branch;

        $settings = $this->getSettings($activeBranch);

        return view('admin.deposit.deposit-print', compact('records', 'settings'));
    }

    // Function 2: For future use (e.g., Export)
    public function export($ids) 
    {
        // Your logic for Excel or CSV export would go here
        return "Exporting records: " . $ids;
    }

    // Private helper to keep code clean
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