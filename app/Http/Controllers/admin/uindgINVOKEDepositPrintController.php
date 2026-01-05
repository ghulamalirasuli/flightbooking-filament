<?php

namespace App\Http\Controllers\admin; // Must match the folder path

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\User;
use Illuminate\Http\Request;

class DepositPrintController extends Controller
{
    /**
     * The __invoke method makes this a single-action controller.
     */
    public function __invoke($ids)
    {
        $idArray = explode(',', $ids);
        
        $records = CashBox::with([
            'branch', 
            'user', 
            'account.accountType', 
            'account.branch', 
            'currency'
        ])->whereIn('id', $idArray)->get();

        $transactionBranch = $records->first()?->branch;
        $userBranch = auth()->user()->branch; 
        $activeBranch = $transactionBranch ?? $userBranch;

        if (!$activeBranch) {
            $admin = User::where('is_admin', 1)->first();
            $settings = [
                'logo'  => asset('images/logo.png'), 
                'email' => $admin?->email ?? 'email@email.com',
                'name'  => $admin?->name ?? 'Head Office',
                'phone' => $admin?->mobile_number ?? '0799554258',
            ];
        } else {
            $settings = [
                'logo'  => $activeBranch->logo ? asset('storage/' . $activeBranch->logo) : asset('images/logo.png'), 
                'email' => $activeBranch->email ?? $activeBranch->branch_email, 
                'name'  => $activeBranch->branch_name,
                'phone' => $activeBranch->whatsapp ?? '',
            ];
        }

        return view('admin.deposit.deposit-print', compact('records', 'settings'));
    }
}