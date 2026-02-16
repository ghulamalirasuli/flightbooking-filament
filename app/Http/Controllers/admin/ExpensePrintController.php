<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpensePrintController extends Controller
{
    public function print(Request $request)
    {
        // Eager load relationships to prevent N+1 queries
        $query = Expense::with(['user', 'expenseType', 'accountExp', 'currencyExp', 'updated_by']);

        // Retrieve the filter state passed from Filament
        $filters = $request->input('filters', []);

        // 1. Apply Date Range Filter
        if (!empty($filters['date_range']['date_confirm_from'])) {
            $query->whereDate('date_confirm', '>=', $filters['date_range']['date_confirm_from']);
        }
        if (!empty($filters['date_range']['date_confirm_until'])) {
            $query->whereDate('date_confirm', '<=', $filters['date_range']['date_confirm_until']);
        }

        // 2. Apply Entry Type Filter
        if (!empty($filters['entry_type']['value'])) {
            $query->where('entry_type', $filters['entry_type']['value']);
        }

        // 3. Apply Currency Filter
        if (!empty($filters['currency']['value'])) {
            $query->where('currency', $filters['currency']['value']);
        }

        // 4. Apply Status Filter (Accounts for your default 'Pending' state)
        if (!empty($filters['status']['value'])) {
            $query->where('status', $filters['status']['value']);
        }

        // Fetch the filtered records
        $expenses = $query->latest()->get();

        return view('admin.expense.print', compact('expenses', 'filters'));
    }
}