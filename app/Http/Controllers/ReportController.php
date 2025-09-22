<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function budgetRealization(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');
        $pic = $request->get('pic');

        $query = BudgetCategory::query();

        if ($pic) {
            $query->where('pic', $pic);
        }

        $budgets = $query->get();

        $summary = [
            'total_budget' => $budgets->sum('budget_allocation'),
            'total_realization' => $budgets->sum('total_penyerapan'),
            'total_outstanding' => $budgets->sum('tagihan_outstanding'),
        ];

        $summary['realization_percentage'] = $summary['total_budget'] > 0
            ? ($summary['total_realization'] / $summary['total_budget']) * 100
            : 0;

        $pics = BudgetCategory::distinct()->pluck('pic');

        if ($request->get('format') === 'pdf') {
            return view('reports.budget-realization-pdf', compact('budgets', 'summary', 'year', 'month', 'pic'));
        }

        return view('reports.budget-realization', compact('budgets', 'summary', 'year', 'month', 'pic', 'pics'));
    }

    public function billsReport(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');
        $status = $request->get('status');

        $query = Bill::with(['budgetCategory', 'creator']);

        if ($year) {
            $query->where('year', $year);
        }

        if ($month) {
            $query->where('month', $month);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $bills = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_bills' => $bills->count(),
            'sp2d_count' => $bills->where('status', 'sp2d')->count(),
            'pending_count' => $bills->where('status', 'pending')->count(),
            'cancelled_count' => $bills->where('status', 'cancelled')->count(),
            'sp2d_amount' => $bills->where('status', 'sp2d')->sum('amount'),
            'pending_amount' => $bills->where('status', 'pending')->sum('amount'),
        ];

        if ($request->get('format') === 'pdf') {
            return view('reports.bills-pdf', compact('bills', 'summary', 'year', 'month', 'status'));
        }

        return view('reports.bills', compact('bills', 'summary', 'year', 'month', 'status'));
    }

    public function monthlyComparison(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // Fix: Use correct column names from database
        $monthFieldMapping = [
            1 => 'realisasi_jan',
            2 => 'realisasi_feb',
            3 => 'realisasi_mar',
            4 => 'realisasi_apr',
            5 => 'realisasi_mei',  // Fixed: mei not may
            6 => 'realisasi_jun',
            7 => 'realisasi_jul',
            8 => 'realisasi_agu',  // Fixed: agu not aug
            9 => 'realisasi_sep',
            10 => 'realisasi_okt', // Fixed: okt not oct
            11 => 'realisasi_nov',
            12 => 'realisasi_des', // Fixed: des not dec
        ];

        $monthlyData = collect(range(1, 12))->map(function ($month) use ($year, $monthFieldMapping) {
            $billsInMonth = Bill::where('month', $month)->where('year', $year);

            // Get realization using correct column name
            $realizationField = $monthFieldMapping[$month];
            $realization = BudgetCategory::sum($realizationField);

            return [
                'month' => $month,
                'month_name' => $this->getIndonesianMonthName($month),
                'realization' => $realization,
                'bills_count' => $billsInMonth->count(),
                'sp2d_count' => $billsInMonth->where('status', 'sp2d')->count(),
                'pending_count' => $billsInMonth->where('status', 'pending')->count(),
            ];
        });

        if ($request->get('format') === 'pdf') {
            return view('reports.monthly-comparison-pdf', compact('monthlyData', 'year'));
        }

        return view('reports.monthly-comparison', compact('monthlyData', 'year'));
    }

    /**
     * Get Indonesian month name
     */
    private function getIndonesianMonthName($month)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$month] ?? 'Unknown';
    }

    /**
     * Trend Analysis Report
     */
    public function trendAnalysis(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $compareYear = $request->get('compare_year', $year - 1);

        // Fixed: Use correct column names
        $monthFieldMapping = [
            1 => 'realisasi_jan',
            2 => 'realisasi_feb',
            3 => 'realisasi_mar',
            4 => 'realisasi_apr',
            5 => 'realisasi_mei',
            6 => 'realisasi_jun',
            7 => 'realisasi_jul',
            8 => 'realisasi_agu',
            9 => 'realisasi_sep',
            10 => 'realisasi_okt',
            11 => 'realisasi_nov',
            12 => 'realisasi_des',
        ];

        // Get trend data with correct column names
        $trendData = collect(range(1, 12))->map(function ($month) use ($year, $compareYear, $monthFieldMapping) {
            $currentField = $monthFieldMapping[$month];

            // Current year realization
            $currentRealization = BudgetCategory::sum($currentField);

            // Previous year realization (if needed for comparison)
            $previousRealization = 0; // You can implement this if needed

            // Calculate growth
            $growth = $previousRealization > 0
                ? (($currentRealization - $previousRealization) / $previousRealization) * 100
                : 0;

            return [
                'month' => $month,
                'month_name' => $this->getIndonesianMonthName($month),
                'current_realization' => $currentRealization,
                'previous_realization' => $previousRealization,
                'growth_percentage' => $growth,
            ];
        });

        $summary = [
            'total_current' => $trendData->sum('current_realization'),
            'total_previous' => $trendData->sum('previous_realization'),
            'average_growth' => $trendData->avg('growth_percentage'),
            'highest_month' => $trendData->sortByDesc('current_realization')->first(),
            'lowest_month' => $trendData->sortBy('current_realization')->first(),
        ];

        if ($request->get('format') === 'pdf') {
            return view('reports.trend-analysis-pdf', compact('trendData', 'summary', 'year', 'compareYear'));
        }

        return view('reports.trend-analysis', compact('trendData', 'summary', 'year', 'compareYear'));
    }
}
