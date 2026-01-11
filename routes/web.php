<?php

use Illuminate\Support\Facades\Route;
use App\Models\CashBox;


use App\Http\Controllers\admin\AccountLedgerPrintController;

// Top of web.php
use App\Http\Controllers\admin\DepositPrintController;
use App\Http\Controllers\admin\MTPrintController;

//1--- The route using invoke function
// Route::get('/admin/deposits/print/{ids}', DepositPrintController::class)
//     ->name('deposits.print')
//     ->middleware(['auth']);
//2-- uisng normal controller

Route::get('/admin/transfer/print/{record}', [MTPrintController::class, 'print'])
    ->name('transfer.print')->middleware(['auth']);

Route::get('/admin/transfer/printall', [MTPrintController::class, 'print_all'])
    ->name('transfer.print_all')
    ->middleware(['auth']);


// Route for Printing
Route::get('/admin/deposits/print/{record}', [DepositPrintController::class, 'print'])
    ->name('deposits.print')->middleware(['auth']);

Route::get('/admin/deposits/printall', [DepositPrintController::class, 'print_all'])
    ->name('deposits.print_all')
    ->middleware(['auth']);
    
Route::get('/admin/account_ledger/ledger-print/{ownerId}', [AccountLedgerPrintController::class, 'print'])
    ->name('account_ledger.print')
    ->middleware(['web', 'auth']); // Ensure only logged-in users can print

// In your web.php or api.php routes file
Route::get('/admin/account_ledger/send-email/{ownerId}', [AccountLedgerPrintController::class, 'sendEmail'])
    ->name('account_ledger.send_email')
    ->middleware(['web', 'auth']);



// Route::get('/admin/deposits/print/{record}', function (CashBox $record) {
//     return view('print.deposit-print', ['record' => $record]);
// })->name('deposits.print')->middleware(['web','auth']);
// routes/web.php
// routes/web.php

// Route::get('/admin/deposits/print/{ids}', function ($ids) {
//     $idArray = explode(',', $ids);
    
//     // Notice the nested eager loading for accountType and account branch
//     $records = \App\Models\CashBox::with([
//         'branch', 
//         'user', 
//         'account.accountType', 
//         'account.branch', 
//         'currency'
//     ])->whereIn('id', $idArray)->get();

//     $transactionBranch = $records->first()?->branch;
//     $userBranch = auth()->user()->branch; 
//     $activeBranch = $transactionBranch ?? $userBranch;

//     if (!$activeBranch) {
//         $admin = \App\Models\User::where('is_admin', 1)->first();
//         $settings = [
//             'logo'  => asset('images/logo.png'), 
//             'email' => $admin?->email ?? 'email@email.com',
//             'name'  => $admin?->name ?? 'Head Office',
//             'phone' => $admin?->mobile_number ?? '0799554258',
//         ];
//     } else {
//         $settings = [
//             'logo'  => $activeBranch->logo ? asset('storage/' . $activeBranch->logo) : asset('images/logo.png'), 
//             'email' => $activeBranch->email ?? $activeBranch->branch_email, 
//             'name'  => $activeBranch->branch_name,
//             'phone' => $activeBranch->whatsapp ?? '',
//         ];
//     }

//     return view('print.deposit-print', compact('records', 'settings'));
// })->name('deposits.print')->middleware(['auth']);

Route::get('/', function () {
    return view('welcome');
});
