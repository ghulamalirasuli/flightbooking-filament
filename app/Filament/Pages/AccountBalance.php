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
use Filament\Support\Icons\Heroicon;

class AccountBalance extends Page implements HasForms
{
    
    use InteractsWithForms;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-scale';
    protected static ?string $title = 'Account Balance';

    protected static ?string $navigationLabel = 'Account Balance';
    protected static ?string $recordTitleAttribute = 'Account Balance';

    protected static ?string $modelLabel = 'Account Balance';
    
    protected static ?string $pluralModelLabel = 'Account Balances';
    protected string $view = 'filament.pages.account-balance';
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Account Selection')
                    ->schema([
                        Select::make('selectedBranch')
                            ->label('Branch')
                            ->options(Branch::all()->pluck('branch_name', 'id'))
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('selectedAccount', null)),

                        Select::make('selectedAccount')
                            ->label('Account')
                            ->options(function ($get) {
                                $branchId = $get('selectedBranch');
                                if (!$branchId) return [];
                                return Accounts::where('branch_id', $branchId)
                                    ->get()
                                    ->pluck('account_name_with_category_and_branch', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->live(),
                    ])->columns(2)
            ])
            ->statePath('data');
    }

    /**
     * Computed property to get unified data for the table
     */
    public function getUnifiedBalancesProperty()
{
    $accountId = $this->data['selectedAccount'] ?? null;
    if (!$accountId) return collect();

    $account = Accounts::find($accountId);
    if (!$account) return collect();

    $allCurrencies = Currency::all();
    
    $ledgerData = Account_ledger::where('account', $account->uid)
        ->select('account', 'currency', 'status', DB::raw('SUM(credit) - SUM(debit) as balance'))
        // Add 'account' to the groupBy here:
        ->groupBy('account', 'currency', 'status') 
        ->get();

    return $allCurrencies->map(function ($currency) use ($ledgerData) {
        return [
            'id' => $currency->id,
            'name' => $currency->currency_name,
            'code' => $currency->currency_code,
            'confirmed' => $ledgerData->where('currency', $currency->id)->where('status', 'Confirmed')->first()?->balance ?? 0,
            'pending' => $ledgerData->where('currency', $currency->id)->where('status', 'Pending')->first()?->balance ?? 0,
        ];
    });
}


    public function render(): \Illuminate\Contracts\View\View
{
    return view($this->view, [
        'selectedAccountUid' => $this->data['selectedAccount'] ?? null,
        'unifiedBalances' => $this->unifiedBalances, 
    ])
    ->layout('filament-panels::components.layout.index', [
        'title' => 'Account Balance', // This forces the title into the layout
    ]); 
}


}