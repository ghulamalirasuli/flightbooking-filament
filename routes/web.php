<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AccountLedgerPrintController;

Route::get('/account_ledger/ledger-print/{ownerId}', [AccountLedgerPrintController::class, 'print'])
    ->name('account_ledger.print')
    ->middleware(['web', 'auth']); // Ensure only logged-in users can print

// In your web.php or api.php routes file
Route::get('/account_ledger/send-email/{ownerId}', [AccountLedgerPrintController::class, 'sendEmail'])
    ->name('account_ledger.send_email')
    ->middleware(['web', 'auth']);

Route::get('/', function () {
    return view('welcome');
});
