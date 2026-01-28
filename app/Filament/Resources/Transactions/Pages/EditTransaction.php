<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Transactions\Schemas\TransactionEditForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Account_ledger;
use App\Models\Income_expense;
use App\Models\Currency;
use Filament\Actions\Action;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    /**
     * Override to use the single-record edit form
     */
    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return TransactionEditForm::configure($schema);
    }

    /**
     * Custom update logic for single transaction and related ledgers
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // Recalculate profit with current currency rates
            $fcur = Currency::where('id', $data['from_currency'])->value('buy_rate');
            $tcur = Currency::where('id', $data['to_currency'])->value('sell_rate');
            
            if (!$fcur || !$tcur) {
                throw new \Exception('Currency rate not found. Please verify currency settings.');
            }

            $f_price = $data['fixed_price'] / $fcur;
            $s_price = $data['sold_price'] / $tcur;
            $profit = $s_price - $f_price;

            // Update main transaction
            $record->update([
                'branch_id' => $data['branch_id'],
                'service_type' => $data['service_type'],
                'account_from' => $data['account_from'],
                'account_to' => $data['account_to'],
                'from_currency' => $data['from_currency'],
                'to_currency' => $data['to_currency'],
                'fixed_price' => $data['fixed_price'],
                'sold_price' => $data['sold_price'],
                'profit' => $profit,
                'fullname' => $data['fullname'],
                'doc_number' => $data['doc_number'],
                'doc_type' => $data['doc_type'],
                'description' => $data['description'],
                'depart_date' => $data['depart_date'],
                'arrival_date' => $data['arrival_date'],
                'delivery_date' => $data['delivery_date'],
                'pay_status' => 'Unpaid',
                'date_update' => now(),
                'update_by' => Auth::id(),
            ]);

            // Update FROM Account Ledger (credit side)
            Account_ledger::where('uid', $record->uid)
                ->where('credit', '>', 0)
                ->update([
                    'account' => $data['account_from'],
                    'description' => $data['description'] ?? 'Transaction Entry',
                    'credit' => $data['fixed_price'],
                    'currency' => $data['from_currency'],
                    'branch_id' => $data['branch_id'],
                    'date_update' => now(),
                ]);

            // Update TO Account Ledger (debit side)
            Account_ledger::where('uid', $record->uid)
                ->where('debit', '>', 0)
                ->update([
                    'account' => $data['account_to'],
                    'description' => $data['description'] ?? 'Transaction Entry',
                    'debit' => $data['sold_price'],
                    'currency' => $data['to_currency'],
                    'branch_id' => $data['branch_id'],
                    'date_update' => now(),
                ]);

            // Update Income/Expense entry
            Income_expense::where('uid', $record->uid)
                ->where('type', 'Income')
                ->update([
                    'description' => 'Cost for: ' . ($data['fullname'] ?? 'Service'),
                    'credit' => $profit,
                    'branch_id' => $data['branch_id'],
                    'date_update' => now(),
                ]);

            return $record;
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            //  Action::make('back')
            //     ->label('Back')
            //     ->color('gray')
            //     ->url($this->getResource()::getUrl('index')),
            // ViewAction::make(),
            // DeleteAction::make(),
            // ForceDeleteAction::make(),
            // RestoreAction::make(),
        ];
    }

}