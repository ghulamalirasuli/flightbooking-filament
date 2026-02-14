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

use App\Models\ContactInfo;
use App\Models\User;
use App\Models\Branch;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // <-- ADD THIS IMPORT

use Carbon\Carbon;

class Contacts extends Page
{
    use InteractsWithForms;
    use WithPagination;
    
    protected string $view = 'filament.pages.contacts';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 3;
    protected static string | UnitEnum | null $navigationGroup = 'Transactions';

    public ?array $data = [];
    
    #[Url(as: 'per_page', keep: true)]
    public int $perPage = 10;

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
                                        $set('User', null);
                                        $this->resetPage();
                                    })
                                    ->columnSpan(2),

                                Select::make('User')
                                    ->label('Users')
                                    ->options(function ($get) {
                                        $branchId = $get('selectedBranch');
                                        if (!$branchId) {
                                            return [];
                                        }
                                        return User::where('branch_id', $branchId)
                                            ->get()
                                            ->mapWithKeys(fn($user) => [$user->id => $user->name])
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
                    ])
                    ->compact(),
            ])
            ->statePath('data');
    }

    // ADD THE #[Computed] ATTRIBUTE HERE
    #[Computed]
    public function getContacts(): LengthAwarePaginator
    {
        $query = ContactInfo::query();

        // Account filter
        if (!empty($this->data['User'])) {
            $userid = User::where('id', $this->data['User'])->value('id');
            if ($userid) {
                $query->where('user_id', $userid);
            }
        }

        // Date range filter
        if (!empty($this->data['date_from']) && !empty($this->data['date_to'])) {
            $from = Carbon::parse($this->data['date_from'])->startOfDay();
            $to = Carbon::parse($this->data['date_to'])->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        } elseif (!empty($this->data['date_from'])) {
            $from = Carbon::parse($this->data['date_from'])->startOfDay();
            $query->where('created_at', '>=', $from);
        } elseif (!empty($this->data['date_to'])) {
            $to = Carbon::parse($this->data['date_to'])->endOfDay();
            $query->where('created_at', '<=', $to);
        }

        $contactData = $query
            ->select('user_id', 'branch_id', 'reference_no', 'fullname', 'email', 'mobile_number','created_at') // Changed 'user' to 'user_id'
            ->with('user')
            ->get();

        // Transform data
        $transformedData = $contactData->map(function ($item) {
            return [
                'user' => $item->user?->name ?? 'Unknown',
                'branch' => $item->branch?->branch_name ?? 'Unknown',
                'reference_no' => $item->reference_no,
                'fullname' => $item->fullname,
                'email' => $item->email,
                'mobile_number' => $item->mobile_number,
                'created_at' => $item->created_at,
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

    public function previousPage(): void
    {
        $this->setPage(max(1, $this->getPage() - 1));
    }

    public function nextPage(): void
    {
        $this->setPage(min($this->getContacts->lastPage(), $this->getPage() + 1));
    }

    public function gotoPage(int $page): void
    {
        $this->setPage(max(1, min($page, $this->getContacts->lastPage())));
    }

    public function render(): View
    {
        return view($this->view, [
            'user' => $this->data['User'] ?? null,
            'getContacts' => $this->getContacts, // Now this works with #[Computed]
        ])
        ->layout('filament-panels::components.layout.index', [
            'title' => 'Contacts',
        ]);
    }
}