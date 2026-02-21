<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IncomeLedgers\Tables\IncomeLedgersTable;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class IncomeExpenseChart extends ApexChartWidget
{
    use HasFiltersSchema;

    protected static ?string $chartId = 'incomeExpenseChart';
    protected static ?string $heading = 'Income & Expense Analysis';
    protected static ?int $contentHeight = 400;
    protected int | string | array $columnSpan = 'full';

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('from_date')
                ->label('From Date')
                ->default(now()->startOfMonth()),
            
            DatePicker::make('to_date')
                ->label('To Date')
                ->default(now()),
            
            Select::make('branch_id')
                ->label('Branch')
                ->options(fn () => \App\Models\Branch::pluck('branch_name', 'uid'))
                ->visible(fn () => auth()->user()->user_type == "Superuser" || auth()->user()->user_type == "Admin")
                ->native(false),
        ]);
    }

    protected function getOptions(): array
    {
        $fromDate = $this->filters['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate = $this->filters['to_date'] ?? now()->format('Y-m-d');
        $branchId = $this->filters['branch_id'] ?? auth()->user()->branch_id;

        $chartData = IncomeLedgersTable::getChartData($fromDate, $toDate, $branchId);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'stacked' => true,
                'toolbar' => ['show' => true],
            ],
            'series' => $chartData['income']['series'] ?? [],
            'xaxis' => [
                'categories' => $chartData['income']['categories'] ?? [],
                'title' => ['text' => 'Services'],
            ],
            'yaxis' => [
                'title' => ['text' => 'Amount'],
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
            ],
            'fill' => ['opacity' => 1],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
            'title' => [
                'text' => 'Income by Service & Currency',
                'align' => 'center',
            ],
        ];
    }
}