<?php
// app/Http/Controllers/ReportController.php
namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function budgetRealization(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month');
            $pic = $request->get('pic');

            $query = BudgetCategory::where('is_active', true);

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

            $pics = BudgetCategory::where('is_active', true)->distinct()->pluck('pic');

            if ($request->get('format') === 'pdf') {
                return view('reports.budget-realization-pdf', compact('budgets', 'summary', 'year', 'month', 'pic'));
            }

            return view('reports.budget-realization', compact('budgets', 'summary', 'year', 'month', 'pic', 'pics'));
        } catch (\Exception $e) {
            Log::error('Budget realization report error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat laporan realisasi anggaran.');
        }
    }

    public function billsReport(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month');
            $status = $request->get('status');

            $query = Bill::with(['budgetCategory', 'creator']);

            if ($year) {
                $query->byYear($year);
            }

            if ($month) {
                $query->byMonth($month);
            }

            if ($status) {
                $query->byStatus($status);
            }

            $bills = $query->orderBy('created_at', 'desc')->get();

            $summary = [
                'total_bills' => $bills->count(),
                'sp2d_count' => $bills->where('status', 'Tagihan Telah SP2D')->count(),
                'pending_count' => $bills->whereNotIn('status', ['Tagihan Telah SP2D', 'cancelled'])->count(),
                'cancelled_count' => $bills->where('status', 'cancelled')->count(),
                'sp2d_amount' => $bills->where('status', 'Tagihan Telah SP2D')->sum('amount'),
                'pending_amount' => $bills->whereNotIn('status', ['Tagihan Telah SP2D', 'cancelled'])->sum('amount'),
            ];

            if ($request->get('format') === 'pdf') {
                return view('reports.bills-pdf', compact('bills', 'summary', 'year', 'month', 'status'));
            }

            return view('reports.bills', compact('bills', 'summary', 'year', 'month', 'status'));
        } catch (\Exception $e) {
            Log::error('Bills report error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat laporan tagihan.');
        }
    }

    public function monthlyComparison(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));

            // Fix: Use correct column names from database
            $monthFieldMapping = [
                1 => 'realisasi_jan', 2 => 'realisasi_feb', 3 => 'realisasi_mar',
                4 => 'realisasi_apr', 5 => 'realisasi_mei', 6 => 'realisasi_jun',
                7 => 'realisasi_jul', 8 => 'realisasi_agu', 9 => 'realisasi_sep',
                10 => 'realisasi_okt', 11 => 'realisasi_nov', 12 => 'realisasi_des',
            ];

            $monthlyData = collect(range(1, 12))->map(function ($month) use ($year, $monthFieldMapping) {
                $billsInMonth = Bill::where('month', $month)->where('year', $year);

                // Get realization using correct column name
                $realizationField = $monthFieldMapping[$month];
                $realization = BudgetCategory::where('is_active', true)->sum($realizationField);

                return [
                    'month' => $month,
                    'month_name' => $this->getIndonesianMonthName($month),
                    'realization' => (float) $realization,
                    'bills_count' => $billsInMonth->count(),
                    'sp2d_count' => $billsInMonth->where('status', 'Tagihan Telah SP2D')->count(),
                    'pending_count' => $billsInMonth->whereNotIn('status', ['Tagihan Telah SP2D', 'cancelled'])->count(),
                ];
            });

            if ($request->get('format') === 'pdf') {
                return view('reports.monthly-comparison-pdf', compact('monthlyData', 'year'));
            }

            return view('reports.monthly-comparison', compact('monthlyData', 'year'));
        } catch (\Exception $e) {
            Log::error('Monthly comparison report error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat laporan perbandingan bulanan.');
        }
    }

    private function getIndonesianMonthName($month)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $months[$month] ?? 'Unknown';
    }

    public function trendAnalysis(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $compareYear = $request->get('compare_year', $year - 1);

            $monthFieldMapping = [
                1 => 'realisasi_jan', 2 => 'realisasi_feb', 3 => 'realisasi_mar',
                4 => 'realisasi_apr', 5 => 'realisasi_mei', 6 => 'realisasi_jun',
                7 => 'realisasi_jul', 8 => 'realisasi_agu', 9 => 'realisasi_sep',
                10 => 'realisasi_okt', 11 => 'realisasi_nov', 12 => 'realisasi_des',
            ];

            $trendData = collect(range(1, 12))->map(function ($month) use ($year, $compareYear, $monthFieldMapping) {
                $currentField = $monthFieldMapping[$month];

                // Current year realization
                $currentRealization = BudgetCategory::where('is_active', true)->sum($currentField);

                // Previous year realization - simplified approach
                $previousRealization = 0;

                // Calculate growth
                $growth = $previousRealization > 0
                    ? (($currentRealization - $previousRealization) / $previousRealization) * 100
                    : 0;

                return [
                    'month' => $month,
                    'month_name' => $this->getIndonesianMonthName($month),
                    'current_realization' => (float) $currentRealization,
                    'previous_realization' => (float) $previousRealization,
                    'growth_percentage' => round($growth, 2),
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
        } catch (\Exception $e) {
            Log::error('Trend analysis report error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat laporan analisis trend.');
        }
    }
}
