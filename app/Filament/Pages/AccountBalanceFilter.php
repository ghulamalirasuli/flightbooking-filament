<?php

namespace App\Filament\Pages;


use App\Models\Branch;
use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Account_ledger;
use Filament\Schemas\Schema; 
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use BackedEnum;
use Carbon\Carbon;


class AccountBalanceFilter extends Page implements HasForms
{
    use InteractsWithForms;

     protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $title = 'Account Balance Filter';
    protected static ?string $navigationLabel = 'Account Balance Filter';
    protected static ?string $recordTitleAttribute = 'Account Balance Filter';
    protected static ?string $modelLabel = 'Account Balance Filter';
    protected static ?string $pluralModelLabel = 'Account Balances Filter';

    protected string $view = 'filament.pages.account-balance-filter';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Account Selection')->schema([
                    Select::make('selectedBranch')
                        ->label('Branch')
                        ->options(Branch::all()->pluck('branch_name', 'id'))
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('selectedAccount', null);
                        }),

                    Select::make('selectedAccount')
                        ->label('Account')
                        ->options(function ($get) {
                            $branchId = $get('selectedBranch');
                            if (!$branchId) return [];

                            // Fetch models first, then use accessor
                            return Accounts::where('branch_id', $branchId)
                                ->get()
                                ->mapWithKeys(fn($account) => [
                                    $account->id => $account->account_name_with_category_and_branch
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required()
                        ->reactive(),

                    Select::make('tpe')
                        ->label('Deposit Type')
                        ->options([
                            'Cash' => 'Cash',
                            'Invoice' => 'Invoice',
                            'Transfer' => 'Transfer',
                        ])
                        ->default('Cash')
                        ->required()
                        ->reactive(),
                ])->columns(3),
            ])
            ->statePath('data');
    }

    public function getUnifiedBalancesProperty()
{
    $query = Account_ledger::query();

    // Filter by selected account if set
    if (!empty($this->data['selectedAccount'])) {
        $query->where('account', $this->data['selectedAccount']);
    }

    $ledgerData = $query
        ->select('account', 'reference_no', 'currency', DB::raw('SUM(credit) - SUM(debit) as balance'))
        ->groupBy('account', 'reference_no', 'currency')
        ->get();

    // Get all accounts keyed by UID (important!)
    $accounts = Accounts::all()->keyBy('uid');

    // Get all active currencies
    $currencies = Currency::where('status', true)->get();

    // Prepare table rows
    $tableData = $ledgerData
        ->groupBy(['account', 'reference_no'])
        ->map(function ($refs, $accountUid) use ($currencies, $accounts) {
            return $refs->map(function ($ledgerItems) use ($currencies, $accountUid, $accounts) {
                $row = [
                    'account_name' => $accounts[$accountUid]->account_name_with_category_and_branch ?? 'Unknown',
                    'reference_no' => $ledgerItems->first()->reference_no,
                ];

                foreach ($currencies as $currency) {
                    $balance = $ledgerItems
                        ->where('currency', $currency->id)
                        ->sum('balance');
                    $row[$currency->currency_name] = $balance;
                }

                return $row;
            });
        })
        ->flatten(1);

    return $tableData;
}


    public function render(): View
    {
        return view($this->view, [
            'selectedAccountUid' => $this->data['selectedAccount'] ?? null,
            'unifiedBalances' => $this->unifiedBalances,
            'currencies' => Currency::where('status', true)->get(),
        ])
        ->layout('filament-panels::components.layout.index', [
            'title' => 'Account Balance Filter',
        ]);
    }
}
