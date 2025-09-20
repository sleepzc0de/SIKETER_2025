<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\BudgetRealization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = date('Y');

        // Total Budget Allocation
        $totalBudget = BudgetCategory::where('is_active', true)->sum('budget_allocation');

        // Total Realization
        $totalRealization = BudgetRealization::whereYear('created_at', $currentYear)->sum('amount');

        // Outstanding Bills
        $totalOutstanding = BudgetRealization::whereYear('created_at', $currentYear)->sum('outstanding_bills');

        // Remaining Budget
        $remainingBudget = $totalBudget - $totalRealization;

        // Realization Percentage
        $realizationPercentage = $totalBudget > 0 ? ($totalRealization / $totalBudget) * 100 : 0;

        // Monthly Realization Data for Chart
        $monthlyRealization = BudgetRealization::select(
            DB::raw('EXTRACT(month FROM created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
        ->whereYear('created_at', $currentYear)
        ->groupBy(DB::raw('EXTRACT(month FROM created_at)'))
        ->orderBy('month')
        ->get();

        // Top 5 Budget Categories by Realization
        $topCategories = BudgetCategory::with('budgetRealizations')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->description,
                    'realization' => $category->total_realization,
                    'percentage' => $category->realization_percentage,
                ];
            })
            ->sortByDesc('realization')
            ->take(5);

        // Recent Transactions
        $recentTransactions = BudgetRealization::with(['budgetCategory', 'creator'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalBudget',
            'totalRealization',
            'totalOutstanding',
            'remainingBudget',
            'realizationPercentage',
            'monthlyRealization',
            'topCategories',
            'recentTransactions'
        ));
    }
}
