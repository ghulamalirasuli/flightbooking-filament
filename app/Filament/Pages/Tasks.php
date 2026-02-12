<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

use Filament\Schemas\Schema; 
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

use App\Models\Branch;
use App\Models\Accounts;
use App\Models\Comments;


use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

use Carbon\Carbon;

class Tasks extends Page
{
    use InteractsWithForms;
    use WithPagination;

    protected string $view = 'filament.pages.tasks';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

     protected static ?int $navigationSort = 2;

    protected static string | UnitEnum | null $navigationGroup = 'Transactions';


     public ?array $data = [];
    
    // Use Livewire's URL attribute for query string
    #[Url(as: 'per_page', keep: true)]
    public int $perPage = 10;

    // Pagination page is handled by WithPagination trait
    protected string $paginationTheme = 'tailwind';

    // Labels mapping for display values
    protected array $typeLabels = [
        'Remark' => 'Remark',
        'Task' => 'Task',
        'Reminder' => 'Reminder',
    ];

    protected array $reminderLabels = [
        'yes' => 'Have reminder',
        'no' => 'No reminder',
    ];

    protected array $visibilityLabels = [
        'internal' => 'Internal Only',
        'account' => 'Visible to current Account',
        'all_accounts' => 'Visible to all Accounts',
    ];

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
                                    ->columnSpan(4),
                                
                            DatePicker::make('date_from')
                                    ->label('Date From')
                                    ->live()
                                    ->afterStateUpdated(function () {
                                        $this->resetPage();
                                    })
                                    ->columnSpan(3),

                                DatePicker::make('date_to')
                                    ->label('Date To')
                                    ->live()
                                    ->afterStateUpdated(function () {
                                        $this->resetPage();
                                    })
                                    ->columnSpan(3),    
                                
                            ]),
                            Grid::make()
                               ->columns(12)
                            ->schema([
                                Select::make('type')
                                    ->label('Task Type')
                                    ->options($this->typeLabels)
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->columnSpan(4),

                                Select::make('reminder')
                                    ->label('Reminder')
                                    ->options($this->reminderLabels)
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function () {
                                        $this->resetPage();
                                    })
                                    ->columnSpan(4),

                                     Select::make('visibility')
                                    ->label('Visibility')
                                    ->options($this->visibilityLabels)
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->columnSpan(4),
                            ]),
                    ])
                    ->compact(),
            ])
            ->statePath('data');
    }


public function getUnifiedTasksProperty(): LengthAwarePaginator
{
    $query = Comments::query();

    // Account filter (ID → UID)
    if (!empty($this->data['selectedAccount'])) {
        $accountUid = Accounts::where('id', $this->data['selectedAccount'])->value('uid');

        if ($accountUid) {
            $query->where('account', $accountUid);
        }
    }

    // Date range filter
if (!empty($this->data['date_from']) && !empty($this->data['date_to'])) {

    $from = Carbon::parse($this->data['date_from'])->startOfDay();
    $to   = Carbon::parse($this->data['date_to'])->endOfDay();

    $query->whereBetween('created_at', [$from, $to]);

} elseif (!empty($this->data['date_from'])) {

    $from = Carbon::parse($this->data['date_from'])->startOfDay();

    $query->where('created_at', '>=', $from);

} elseif (!empty($this->data['date_to'])) {

    $to = Carbon::parse($this->data['date_to'])->endOfDay();

    $query->where('created_at', '<=', $to);
}


    // Reminder filter
    if (!empty($this->data['reminder'])) {
        $query->where('reminder', $this->data['reminder']);
    }

    // Type filter
    if (!empty($this->data['type'])) {
        $query->where('type', $this->data['type']);
    }

    // Visibility filter
    if (!empty($this->data['visibility'])) {
        $query->where('visibility', $this->data['visibility']);
    }

    $taskData = $query
        ->select(
            'account',
            'reference_no',
            'type',
            'reminder',
            'visibility',
            'date_comment'
        )
        ->with('relatedAccount')
        ->get();

    // Transform data to include account name and display labels
    $transformedData = $taskData->map(function ($item) {
        return [
            'account' => $item->account,
            'account_name' => $item->relatedAccount?->account_name_with_category_and_branch ?? 'Unknown',
            'reference_no' => $item->reference_no,
            'type' => $item->type,
            'type_label' => $this->typeLabels[$item->type] ?? $item->type,
            'reminder' => $item->reminder,
            'reminder_label' => $this->reminderLabels[$item->reminder] ?? $item->reminder,
            'visibility' => $item->visibility,
            'visibility_label' => $this->visibilityLabels[$item->visibility] ?? $item->visibility,
            'date_comment' => $item->date_comment,
        ];
    });

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
        $this->setPage(min($this->unifiedTasks->lastPage(), $this->getPage() + 1));
    }

    public function gotoPage(int $page): void
    {
        $this->setPage(max(1, min($page, $this->unifiedTasks->lastPage())));
    }

    public function render(): View
    {
        return view($this->view, [
            'selectedAccountUid' => $this->data['selectedAccount'] ?? null,
            'unifiedTasks' => $this->unifiedTasks,
        ])
        ->layout('filament-panels::components.layout.index', [
            'title' => 'Tasks',
        ]);
    }
}