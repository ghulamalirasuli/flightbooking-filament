<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\User;
use App\Models\Account_ledger;
use Illuminate\Http\Request;

class DepositPrintController extends Controller
{
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