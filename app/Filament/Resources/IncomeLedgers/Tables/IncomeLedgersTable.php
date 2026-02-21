<?php

namespace App\Filament\Resources\IncomeLedgers\Tables;

use App\Models\Income_expense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class IncomeLedgersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Your columns here
            ])
            ->filters([
                // Your filters here
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Calculates chart data for IncomeExpenseChart widget.
     */
    public static function getChartData(string $fromDate, string $toDate, $branchId): array
    {
        // Fetch Income grouped by Service
        // Assuming 'credit' is the income amount and 'service_id' links to the service
        $query = Income_expense::query()
            ->with('service')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('credit', '>', 0); // Only income

        // Filter by branch if provided
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Group by service to get totals
        $data = $query->select('service_uid', DB::raw('SUM(credit) as total_amount'))
            ->groupBy('service_uid')
            ->get();

        // Format for ApexCharts
        $categories = [];
        $seriesData = [];

        foreach ($data as $record) {
            // Use service title or fallback to 'Unknown'
            $categories[] = $record->service->title ?? 'Unknown Service';
            $seriesData[] = (float) $record->total_amount;
        }

        return [
            'income' => [
                'categories' => $categories,
                'series' => [
                    [
                        'name' => 'Income',
                        'data' => $seriesData,
                    ]
                ],
            ],
        ];
    }
}