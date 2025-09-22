<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $monthlyData = collect(range(1, 12))->map(function ($month) use ($year) {
            $billsInMonth = Bill::where('month', $month)->where('year', $year);

            return [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'realization' => BudgetCategory::sum("realisasi_" . strtolower(date('M', mktime(0, 0, 0, $month, 1)))),
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
}
