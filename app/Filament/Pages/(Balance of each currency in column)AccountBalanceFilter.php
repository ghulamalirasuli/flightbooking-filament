<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Accounts;
use App\Models\Currency;
use App\Models\Account_ledger;
use Filament\Schemas\Schema; 
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use BackedEnum;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class AccountBalanceFilter extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $title = 'Account Balance Filter';
    protected static ?string $navigationLabel = 'Account Balance Filter';
    protected static ?string $recordTitleAttribute = 'Account Balance Filter';
    protected static ?string $modelLabel = 'Account Balance Filter';
    protected static ?string $pluralModelLabel = 'Account Balances Filter';

    protected string $view = 'filament.pages.account-balance-filter';

    public ?array $data = [];
    
    // Use Livewire's URL attribute for query string
    #[Url(as: 'per_page', keep: true)]
    public int $perPage = 10;

    // Pagination page is handled by WithPagination trait
    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Account Selection')
                    ->schema([
                        Grid::make()
                            ->columns(12)
                            ->schema([
                                Select::make('selectedBranch')
                                    ->label('Branch')
                                    ->options(Branch::all()->pluck('branch_name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('selectedAccount', null);
                                        $this->resetPage();
                                    })
                                    ->columnSpan(2),

                                Select::make('selectedAccount')
                                    ->label('Account')
                                    ->options(function ($get) {
                                        $branchId = $get('selectedBranch');
                                        if (!$branchId) {
                                            return [];
                                        }

                                        return Accounts::where('branch_id', $branchId)
                                            ->get()
                                            ->mapWithKeys(fn($account) => [
                                                $account->id => $account->account_name_with_category_and_branch
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function () {
                                        $this->resetPage();
                                    })
                                    ->columnSpan(6),

                                Select::make('table_name')
                                    ->label('Table')
                                    ->options([
                                        'transaction' => 'Transaction',
                                        'deposit' => 'Deposit',
                                        'transfer' => 'Money Transfer',
                                        'account_ledger' => 'Account Ledger',
                                    ])
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function () {
                                        $this->resetPage();
                                    })
                                    ->columnSpan(2),

                                Select::make('type')
                                    ->label('Deposit Type')
                                    ->options([
                                        'Cash' => 'Cash',
                                        'Invoice' => 'Invoice',
                                        'Transfer' => 'Transfer',
                                    ])
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function () {
                                        $this->resetPage();
                                    })
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->compact(),
            ])
            ->statePath('data');
    }

    public function getUnifiedBalancesProperty(): LengthAwarePaginator
    {
        $query = Account_ledger::query();

        // Account filter (ID → UID)
        if (!empty($this->data['selectedAccount'])) {
            $accountUid = Accounts::where('id', $this->data['selectedAccount'])->value('uid');

            if ($accountUid) {
                $query->where('account', $accountUid);
            }
        }

        // Table name filter
        if (!empty($this->data['table_name'])) {
            $query->where('table_name', $this->data['table_name']);
        }

        // Deposit Type filter (pay_status)
        if (!empty($this->data['type'])) {
            $query->where('pay_status', $this->data['type']);
        }

        $ledgerData = $query
            ->select(
                'account',
                'reference_no',
                'currency',
                DB::raw('SUM(credit) - SUM(debit) as balance')
            )
            ->groupBy('account', 'reference_no', 'currency')
            ->get();

        // Transform data
        $accounts = Accounts::all()->keyBy('uid');
        $currencies = Currency::where('status', true)->get();

        $transformedData = $ledgerData
            ->groupBy(['account', 'reference_no'])
            ->map(function ($refs, $accountUid) use ($currencies, $accounts) {
                return $refs->map(function ($ledgerItems) use ($currencies, $accountUid, $accounts) {
                    $row = [
                        'account_name' => $accounts[$accountUid]->account_name_with_category_and_branch ?? 'Unknown',
                        'reference_no' => $ledgerItems->first()->reference_no,
                    ];

                    foreach ($currencies as $currency) {
                        $row[$currency->currency_name] = $ledgerItems
                            ->where('currency', $currency->id)
                            ->sum('balance');
                    }

                    return $row;
                });
            })
            ->flatten(1)
            ->values();

        // Convert to pagination
        $total = $transformedData->count();
        $currentPage = $this->getPage();
        $items = $transformedData->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    // Custom pagination methods for manual control
    public function previousPage(): void
    {
        $this->setPage(max(1, $this->getPage() - 1));
    }

    public function nextPage(): void
    {
        $this->setPage(min($this->unifiedBalances->lastPage(), $this->getPage() + 1));
    }

    public function gotoPage(int $page): void
    {
        $this->setPage(max(1, min($page, $this->unifiedBalances->lastPage())));
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