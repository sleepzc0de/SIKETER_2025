<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

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

        $cacheKey = "budget_realization_report_" . md5(serialize($request->all()));

        $data = Cache::remember($cacheKey, 1800, function () use ($year, $month, $pic) {
            $query = BudgetCategory::query();

            if ($pic) {
                $query->where('pic', $pic);
            }

            $budgets = $query->get();

            $summary = [
                'total_budget' => $budgets->sum('budget_allocation'),
                'total_realization' => $budgets->sum('total_penyerapan'),
                'total_outstanding' => $budgets->sum('tagihan_outstanding'),
                'remaining_budget' => $budgets->sum('sisa_anggaran'),
            ];

            $summary['realization_percentage'] = $summary['total_budget'] > 0
                ? ($summary['total_realization'] / $summary['total_budget']) * 100
                : 0;

            return compact('budgets', 'summary');
        });

        $pics = Cache::remember('distinct_pics', 3600, function () {
            return BudgetCategory::distinct()->pluck('pic');
        });

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.budget-realization-pdf', array_merge($data, [
                'year' => $year,
                'month' => $month,
                'pic' => $pic,
            ]));
            return $pdf->download("laporan-realisasi-anggaran-{$year}.pdf");
        }

        if ($request->get('format') === 'excel') {
            return Excel::download(
                new FinancialReportExport($data['budgets'], $data['summary']),
                "laporan-realisasi-anggaran-{$year}.xlsx"
            );
        }

        return view('reports.budget-realization', array_merge($data, [
            'year' => $year,
            'month' => $month,
            'pic' => $pic,
            'pics' => $pics,
        ]));
    }

    public function billsReport(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');
        $status = $request->get('status');

        $cacheKey = "bills_report_" . md5(serialize($request->all()));

        $data = Cache::remember($cacheKey, 1800, function () use ($year, $month, $status) {
            $query = Bill::with(['budgetCategory', 'creator', 'approver'])
                ->where('year', $year);

            if ($month) {
                $query->where('month', $month);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $bills = $query->orderBy('bill_date', 'desc')->get();

            $summary = [
                'total_bills' => $bills->count(),
                'total_amount' => $bills->sum('amount'),
                'pending_count' => $bills->where('status', 'pending')->count(),
                'sp2d_count' => $bills->where('status', 'sp2d')->count(),
                'cancelled_count' => $bills->where('status', 'cancelled')->count(),
                'pending_amount' => $bills->where('status', 'pending')->sum('amount'),
                'sp2d_amount' => $bills->where('status', 'sp2d')->sum('amount'),
            ];

            return compact('bills', 'summary');
        });

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.bills-pdf', array_merge($data, [
                'year' => $year,
                'month' => $month,
                'status' => $status,
            ]));
            return $pdf->download("laporan-tagihan-{$year}.pdf");
        }

        return view('reports.bills', array_merge($data, [
            'year' => $year,
            'month' => $month,
            'status' => $status,
        ]));
    }

    public function monthlyComparison(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $cacheKey = "monthly_comparison_report_{$year}";

        $data = Cache::remember($cacheKey, 1800, function () use ($year) {
            $monthlyData = [];

            for ($month = 1; $month <= 12; $month++) {
                $monthField = $this->getMonthField($month);

                $monthlyData[] = [
                    'month' => $month,
                    'month_name' => $this->getMonthName($month),
                    'realization' => BudgetCategory::sum($monthField),
                    'bills_count' => Bill::where('year', $year)->where('month', $month)->count(),
                    'sp2d_count' => Bill::where('year', $year)->where('month', $month)->where('status', 'sp2d')->count(),
                    'pending_count' => Bill::where('year', $year)->where('month', $month)->where('status', 'pending')->count(),
                ];
            }

            return $monthlyData;
        });

        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('reports.monthly-comparison-pdf', [
                'monthlyData' => $data,
                'year' => $year,
            ]);
            return $pdf->download("laporan-perbandingan-bulanan-{$year}.pdf");
        }

        return view('reports.monthly-comparison', [
            'monthlyData' => $data,
            'year' => $year,
        ]);
    }

    private function getMonthField($month)
    {
        $fields = [
            1 => 'realisasi_jan', 2 => 'realisasi_feb', 3 => 'realisasi_mar',
            4 => 'realisasi_apr', 5 => 'realisasi_mei', 6 => 'realisasi_jun',
            7 => 'realisasi_jul', 8 => 'realisasi_agu', 9 => 'realisasi_sep',
            10 => 'realisasi_okt', 11 => 'realisasi_nov', 12 => 'realisasi_des'
        ];

        return $fields[$month];
    }

    private function getMonthName($month)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $months[$month];
    }
}
