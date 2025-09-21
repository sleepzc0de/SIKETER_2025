<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private function safeCache($key, $ttl, $callback)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            return $callback();
        }
    }

    public function index()
    {
        $year = date('Y');

        // Dashboard data with safe caching
        $dashboardData = $this->safeCache("dashboard_data_{$year}", 1800, function () use ($year) {
            return [
                'totalBudget' => BudgetCategory::sum('budget_allocation'),
                'totalRealization' => BudgetCategory::sum('total_penyerapan'),
                'totalOutstanding' => BudgetCategory::sum('tagihan_outstanding'),
                'remainingBudget' => BudgetCategory::sum('sisa_anggaran'),
            ];
        });

        $dashboardData['realizationPercentage'] = $dashboardData['totalBudget'] > 0
            ? ($dashboardData['totalRealization'] / $dashboardData['totalBudget']) * 100
            : 0;

        // Monthly realization data with safe caching
        $monthlyRealization = $this->safeCache("monthly_realization_{$year}", 1800, function () {
            return collect([
                ['month' => 1, 'total' => BudgetCategory::sum('realisasi_jan')],
                ['month' => 2, 'total' => BudgetCategory::sum('realisasi_feb')],
                ['month' => 3, 'total' => BudgetCategory::sum('realisasi_mar')],
                ['month' => 4, 'total' => BudgetCategory::sum('realisasi_apr')],
                ['month' => 5, 'total' => BudgetCategory::sum('realisasi_mei')],
                ['month' => 6, 'total' => BudgetCategory::sum('realisasi_jun')],
                ['month' => 7, 'total' => BudgetCategory::sum('realisasi_jul')],
                ['month' => 8, 'total' => BudgetCategory::sum('realisasi_agu')],
                ['month' => 9, 'total' => BudgetCategory::sum('realisasi_sep')],
                ['month' => 10, 'total' => BudgetCategory::sum('realisasi_okt')],
                ['month' => 11, 'total' => BudgetCategory::sum('realisasi_nov')],
                ['month' => 12, 'total' => BudgetCategory::sum('realisasi_des')],
            ]);
        });

        // Top categories with safe caching
        $topCategories = $this->safeCache("top_categories_{$year}", 1800, function () {
            return BudgetCategory::select(
                'program_kegiatan_output as name',
                'total_penyerapan as realization'
            )
            ->where('total_penyerapan', '>', 0)
            ->orderBy('total_penyerapan', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $totalBudget = BudgetCategory::sum('budget_allocation');
                $item->percentage = $totalBudget > 0 ? ($item->realization / $totalBudget) * 100 : 0;
                return $item;
            });
        });

        // Recent bills with safe caching
        $recentBills = $this->safeCache("recent_bills_{$year}", 900, function () {
            return Bill::with(['budgetCategory', 'creator'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($bill) {
                    return [
                        'description' => $bill->budgetCategory->program_kegiatan_output,
                        'amount' => $bill->amount,
                        'month_name' => $bill->month_name,
                        'year' => $bill->year,
                        'creator' => $bill->creator,
                        'status' => $bill->status,
                    ];
                });
        });

        return view('dashboard', array_merge($dashboardData, [
            'monthlyRealization' => $monthlyRealization,
            'topCategories' => $topCategories,
            'recentTransactions' => $recentBills,
        ]));
    }
}
