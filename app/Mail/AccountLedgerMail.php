<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountLedgerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $account;
    public $pdfContent;

    public function __construct($account, $pdfContent)
    {
        $this->account = $account;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this->view('emails.account_ledger')
                    ->attachData($this->pdfContent, 'ledger_' . $this->account->account_name . '_' . now()->format('Ymd') . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}