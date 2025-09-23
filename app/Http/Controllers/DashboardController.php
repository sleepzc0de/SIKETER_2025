<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        // Check if user can view reports
        if (!Auth::user()->canViewReports() && !Auth::user()->isAdmin() && !Auth::user()->isPimpinan()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat dashboard.');
        }

        // Initialize year and month with proper defaults
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));

        // Ensure year is integer
        $year = (int) $year;
        $month = (int) $month;

        // Get user-specific data based on role
        $userRole = Auth::user()->role;
        $userName = Auth::user()->name;

        // Main dashboard data with role-based filtering
        $dashboardData = $this->getDashboardData($year, $userRole, $userName);

        // Monthly realization trend
        $monthlyRealization = $this->getMonthlyRealization($year, $userRole, $userName);

        // Top performing categories (filtered by user permissions)
        $topCategories = $this->getTopCategories($year, $userRole, $userName);

        // Bottom performing categories
        $bottomCategories = $this->getBottomCategories($year, $userRole, $userName);

        // Recent activities (filtered by user permissions)
        $recentActivities = $this->getRecentActivities($userRole, $userName);

        // Bills status distribution
        $billsStatus = $this->getBillsStatusDistribution($year, $userRole, $userName);

        // Monthly bills trend
        $monthlyBillsTrend = $this->getMonthlyBillsTrend($year, $userRole, $userName);

        // PIC performance (only for admin/pimpinan)
        $picPerformance = $this->getPICPerformance($year, $userRole);

        // Quarterly comparison
        $quarterlyComparison = $this->getQuarterlyComparison($year, $userRole, $userName);

        // Budget utilization rate
        $budgetUtilization = $this->getBudgetUtilization($year, $userRole, $userName);

        // Upcoming deadlines and alerts
        $alerts = $this->getAlertsAndDeadlines($userRole, $userName);

        // Performance indicators
        $kpi = $this->getKeyPerformanceIndicators($year, $userRole, $userName);

        // Available years for dropdown
        $availableYears = $this->getAvailableYears();

        // User permissions for frontend
        $userPermissions = [
            'canManageBudget' => Auth::user()->canManageBudget(),
            'canInputBills' => Auth::user()->canInputBills(),
            'canApprove' => Auth::user()->canApprove(),
            'canExportData' => Auth::user()->canExportData(),
            'canManageUsers' => Auth::user()->canManageUsers(),
        ];

        return view('dashboard', compact(
            'dashboardData',
            'monthlyRealization',
            'topCategories',
            'bottomCategories',
            'recentActivities',
            'billsStatus',
            'monthlyBillsTrend',
            'picPerformance',
            'quarterlyComparison',
            'budgetUtilization',
            'alerts',
            'kpi',
            'year',
            'month',
            'availableYears',
            'userPermissions'
        ));
    }

    /**
     * Get available years from data
     */
    private function getAvailableYears()
    {
        $currentYear = date('Y');
        $years = [];

        // Get years from bills data
        $billYears = Bill::distinct()
            ->whereNotNull('year')
            ->pluck('year')
            ->filter()
            ->sort()
            ->values();

        // Create range from 2020 to current year + 1
        for ($y = 2020; $y <= $currentYear + 1; $y++) {
            $years[] = $y;
        }

        // Merge with bill years and remove duplicates
        $allYears = collect($years)
            ->merge($billYears)
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return $allYears;
    }

    /**
     * Get main dashboard summary data with role-based filtering
     */
    private function getDashboardData($year, $userRole, $userName)
    {
        return Cache::remember("dashboard_data_{$year}_{$userRole}_{$userName}_" . date('Y-m-d-H'), 60, function () use ($year, $userRole, $userName) {
            $query = BudgetCategory::active();

            // Filter by user role
            if ($userRole === 'ppk') {
                $query->where('pic', $userName);
            }

            $totalBudget = $query->sum('budget_allocation') ?: 0;
            $totalRealization = $query->sum('total_penyerapan') ?: 0;
            $totalOutstanding = $query->sum('tagihan_outstanding') ?: 0;
            $remainingBudget = $totalBudget - $totalRealization;

            $realizationPercentage = $totalBudget > 0 ? ($totalRealization / $totalBudget) * 100 : 0;
            $outstandingPercentage = $totalBudget > 0 ? ($totalOutstanding / $totalBudget) * 100 : 0;

            // Compare with previous year
            $previousYearRealization = $this->getPreviousYearRealization($year, $userRole, $userName);
            $realizationGrowth = $previousYearRealization > 0
                ? (($totalRealization - $previousYearRealization) / $previousYearRealization) * 100
                : 0;

            $totalCategories = $query->count();

            return [
                'totalBudget' => $totalBudget,
                'totalRealization' => $totalRealization,
                'totalOutstanding' => $totalOutstanding,
                'remainingBudget' => $remainingBudget,
                'realizationPercentage' => round($realizationPercentage, 2),
                'outstandingPercentage' => round($outstandingPercentage, 2),
                'realizationGrowth' => round($realizationGrowth, 2),
                'totalCategories' => $totalCategories,
                'avgRealizationPerCategory' => $totalCategories > 0
                    ? round($totalRealization / $totalCategories, 2)
                    : 0,
            ];
        });
    }

    /**
     * Get previous year realization for comparison
     */
    private function getPreviousYearRealization($currentYear, $userRole, $userName)
    {
        $query = BudgetCategory::active();

        // Filter by user role
        if ($userRole === 'ppk') {
            $query->where('pic', $userName);
        }

        // For now, we'll use current total_penyerapan as proxy
        // In future, you might want to store yearly data separately
        return $query->sum('total_penyerapan') * 0.85; // Assuming 15% growth
    }

    /**
     * Get monthly realization data with role-based filtering
     */
    private function getMonthlyRealization($year, $userRole, $userName)
    {
        return Cache::remember("monthly_realization_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($year, $userRole, $userName) {
            $monthlyData = collect();

            for ($month = 1; $month <= 12; $month++) {
                $monthName = Carbon::create()->month($month)->format('M');
                $fieldName = $this->getMonthFieldName($month);

                $query = BudgetCategory::active();

                // Filter by user role
                if ($userRole === 'ppk') {
                    $query->where('pic', $userName);
                }

                $realization = $query->sum($fieldName) ?: 0;

                $billsQuery = Bill::where('month', $month)->where('year', $year);

                // Filter bills by user role
                if ($userRole === 'ppk') {
                    $billsQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                        $q->where('pic', $userName);
                    });
                }

                $billsCount = $billsQuery->count();
                $sp2dCount = (clone $billsQuery)->where('status', 'sp2d')->count();

                $monthlyData->push([
                    'month' => $month,
                    'month_name' => $monthName,
                    'realization' => $realization,
                    'bills_count' => $billsCount,
                    'sp2d_count' => $sp2dCount,
                    'completion_rate' => $billsCount > 0 ? round(($sp2dCount / $billsCount) * 100, 2) : 0
                ]);
            }

            return $monthlyData;
        });
    }

    /**
     * Get top performing categories with role-based filtering
     */
    private function getTopCategories($year, $userRole, $userName)
    {
        return Cache::remember("top_categories_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($userRole, $userName) {
            $query = BudgetCategory::active()
                ->select([
                    'id',
                    'kro_code',
                    'ro_code',
                    'account_code',
                    'program_kegiatan_output',
                    'budget_allocation',
                    'total_penyerapan',
                    'pic'
                ])
                ->where('budget_allocation', '>', 0);

            // Filter by user role
            if ($userRole === 'ppk') {
                $query->where('pic', $userName);
            }

            return $query->orderByRaw('(total_penyerapan / NULLIF(budget_allocation, 0)) DESC')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    $percentage = $item->budget_allocation > 0
                        ? ($item->total_penyerapan / $item->budget_allocation) * 100
                        : 0;

                    return [
                        'id' => $item->id,
                        'full_code' => "{$item->kro_code}-{$item->ro_code}-{$item->account_code}",
                        'name' => $item->program_kegiatan_output,
                        'budget_allocation' => $item->budget_allocation,
                        'realization' => $item->total_penyerapan,
                        'percentage' => round($percentage, 2),
                        'pic' => $item->pic
                    ];
                });
        });
    }

    /**
     * Get bottom performing categories with role-based filtering
     */
    private function getBottomCategories($year, $userRole, $userName)
    {
        return Cache::remember("bottom_categories_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($userRole, $userName) {
            $query = BudgetCategory::active()
                ->select([
                    'id',
                    'kro_code',
                    'ro_code',
                    'account_code',
                    'program_kegiatan_output',
                    'budget_allocation',
                    'total_penyerapan',
                    'pic'
                ])
                ->where('budget_allocation', '>', 0);

            // Filter by user role
            if ($userRole === 'ppk') {
                $query->where('pic', $userName);
            }

            return $query->orderByRaw('(total_penyerapan / NULLIF(budget_allocation, 0)) ASC')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    $percentage = $item->budget_allocation > 0
                        ? ($item->total_penyerapan / $item->budget_allocation) * 100
                        : 0;

                    return [
                        'id' => $item->id,
                        'full_code' => "{$item->kro_code}-{$item->ro_code}-{$item->account_code}",
                        'name' => $item->program_kegiatan_output,
                        'budget_allocation' => $item->budget_allocation,
                        'realization' => $item->total_penyerapan,
                        'percentage' => round($percentage, 2),
                        'pic' => $item->pic
                    ];
                });
        });
    }

    /**
     * Get recent activities with role-based filtering
     */
    private function getRecentActivities($userRole, $userName)
    {
        return Cache::remember("recent_activities_{$userRole}_{$userName}_" . date('Y-m-d-H'), 30, function () use ($userRole, $userName) {
            $activities = collect();

            // Build bills query based on user role
            $billsQuery = Bill::with(['budgetCategory', 'creator']);

            if ($userRole === 'ppk') {
                $billsQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                    $q->where('pic', $userName);
                });
            }

            // Recent bills
            $recentBills = $billsQuery->orderBy('created_at', 'desc')->take(8)->get();

            foreach ($recentBills as $bill) {
                $activities->push([
                    'type' => 'bill_created',
                    'title' => "Tagihan {$bill->bill_number} dibuat",
                    'description' => $bill->budgetCategory ? $bill->budgetCategory->program_kegiatan_output : 'N/A',
                    'amount' => $bill->amount ?: 0,
                    'user' => $bill->creator ? $bill->creator->name : 'System',
                    'time' => $bill->created_at->toISOString(),
                    'status' => $bill->status,
                    'url' => route('bills.show', $bill->id)
                ]);
            }

            // Recent approvals
            $approvalsQuery = Bill::with(['budgetCategory'])
                ->where('updated_at', '>', Carbon::now()->subDays(7))
                ->where('status', 'sp2d');

            if ($userRole === 'ppk') {
                $approvalsQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                    $q->where('pic', $userName);
                });
            }

            $recentApprovals = $approvalsQuery->orderBy('updated_at', 'desc')->take(5)->get();

            foreach ($recentApprovals as $bill) {
                $activities->push([
                    'type' => 'bill_approved',
                    'title' => "SP2D " . ($bill->sp2d_number ?: 'N/A') . " diterbitkan",
                    'description' => $bill->budgetCategory ? $bill->budgetCategory->program_kegiatan_output : 'N/A',
                    'amount' => $bill->amount ?: 0,
                    'user' => 'System',
                    'time' => $bill->updated_at->toISOString(),
                    'status' => $bill->status,
                    'url' => route('bills.show', $bill->id)
                ]);
            }

            return $activities->sortByDesc('time')->take(10)->values();
        });
    }

    /**
     * Get bills status distribution with role-based filtering
     */
    private function getBillsStatusDistribution($year, $userRole, $userName)
    {
        return Cache::remember("bills_status_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 60, function () use ($year, $userRole, $userName) {
            $query = Bill::where('year', $year);

            if ($userRole === 'ppk') {
                $query->whereHas('budgetCategory', function ($q) use ($userName) {
                    $q->where('pic', $userName);
                });
            }

            $statusData = $query->select('status', DB::raw('count(*) as count'), DB::raw('COALESCE(sum(amount), 0) as total_amount'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            return [
                'pending' => [
                    'count' => $statusData->get('pending')->count ?? 0,
                    'amount' => $statusData->get('pending')->total_amount ?? 0,
                    'color' => '#f59e0b'
                ],
                'sp2d' => [
                    'count' => $statusData->get('sp2d')->count ?? 0,
                    'amount' => $statusData->get('sp2d')->total_amount ?? 0,
                    'color' => '#10b981'
                ],
                'cancelled' => [
                    'count' => $statusData->get('cancelled')->count ?? 0,
                    'amount' => $statusData->get('cancelled')->total_amount ?? 0,
                    'color' => '#ef4444'
                ]
            ];
        });
    }

    /**
     * Get monthly bills trend with role-based filtering
     */
    private function getMonthlyBillsTrend($year, $userRole, $userName)
    {
        return Cache::remember("monthly_bills_trend_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($year, $userRole, $userName) {
            $billsTrend = collect();

            for ($month = 1; $month <= 12; $month++) {
                $monthBills = Bill::where('year', $year)->where('month', $month);

                if ($userRole === 'ppk') {
                    $monthBills->whereHas('budgetCategory', function ($q) use ($userName) {
                        $q->where('pic', $userName);
                    });
                }

                $total = $monthBills->count();
                $pending = (clone $monthBills)->where('status', 'pending')->count();
                $sp2d = (clone $monthBills)->where('status', 'sp2d')->count();
                $cancelled = (clone $monthBills)->where('status', 'cancelled')->count();
                $totalAmount = (clone $monthBills)->sum('amount') ?: 0;

                $billsTrend->push([
                    'month' => $month,
                    'month_name' => Carbon::create()->month($month)->format('M'),
                    'total' => $total,
                    'pending' => $pending,
                    'sp2d' => $sp2d,
                    'cancelled' => $cancelled,
                    'total_amount' => $totalAmount,
                ]);
            }

            return $billsTrend;
        });
    }

    /**
     * Get PIC performance data (only for admin/pimpinan)
     */
    private function getPICPerformance($year, $userRole)
    {
        if (!in_array($userRole, ['admin', 'pimpinan'])) {
            return collect();
        }

        return Cache::remember("pic_performance_{$year}_" . date('Y-m-d'), 180, function () {
            return BudgetCategory::active()
                ->select('pic')
                ->selectRaw('COUNT(*) as categories_count')
                ->selectRaw('COALESCE(SUM(budget_allocation), 0) as total_budget')
                ->selectRaw('COALESCE(SUM(total_penyerapan), 0) as total_realization')
                ->selectRaw('COALESCE(AVG(total_penyerapan / NULLIF(budget_allocation, 0) * 100), 0) as avg_percentage')
                ->groupBy('pic')
                ->orderByDesc('total_realization')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    $efficiency_score = ($item->total_budget > 0)
                        ? round(($item->total_realization / $item->total_budget) * 100, 2)
                        : 0;

                    return [
                        'pic' => $item->pic,
                        'categories_count' => $item->categories_count,
                        'total_budget' => $item->total_budget,
                        'total_realization' => $item->total_realization,
                        'avg_percentage' => round($item->avg_percentage, 2),
                        'efficiency_score' => $efficiency_score
                    ];
                });
        });
    }

    /**
     * Get quarterly comparison with role-based filtering
     */
    private function getQuarterlyComparison($year, $userRole, $userName)
    {
        return Cache::remember("quarterly_comparison_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 180, function () use ($year, $userRole, $userName) {
            $quarters = [
                'Q1' => [1, 2, 3],
                'Q2' => [4, 5, 6],
                'Q3' => [7, 8, 9],
                'Q4' => [10, 11, 12]
            ];

            $quarterlyData = collect();

            foreach ($quarters as $quarter => $months) {
                $realization = 0;
                foreach ($months as $month) {
                    $fieldName = $this->getMonthFieldName($month);
                    $query = BudgetCategory::active();

                    if ($userRole === 'ppk') {
                        $query->where('pic', $userName);
                    }

                    $realization += $query->sum($fieldName) ?: 0;
                }

                $billsQuery = Bill::where('year', $year)->whereIn('month', $months);

                if ($userRole === 'ppk') {
                    $billsQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                        $q->where('pic', $userName);
                    });
                }

                $billsCount = $billsQuery->count();
                $sp2dCount = (clone $billsQuery)->where('status', 'sp2d')->count();

                $quarterlyData->push([
                    'quarter' => $quarter,
                    'realization' => $realization,
                    'bills_count' => $billsCount,
                    'sp2d_count' => $sp2dCount,
                    'completion_rate' => $billsCount > 0 ? round(($sp2dCount / $billsCount) * 100, 2) : 0
                ]);
            }

            return $quarterlyData;
        });
    }

    /**
     * Get budget utilization rate by category with role-based filtering
     */
    private function getBudgetUtilization($year, $userRole, $userName)
    {
        return Cache::remember("budget_utilization_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 180, function () use ($userRole, $userName) {
            $utilizationRanges = [
                'excellent' => ['min' => 80, 'max' => 100],
                'good' => ['min' => 60, 'max' => 79],
                'fair' => ['min' => 40, 'max' => 59],
                'poor' => ['min' => 0, 'max' => 39]
            ];

            $utilization = [];

            foreach ($utilizationRanges as $level => $range) {
                $query = BudgetCategory::active()
                    ->whereRaw('(total_penyerapan / NULLIF(budget_allocation, 0) * 100) BETWEEN ? AND ?',
                        [$range['min'], $range['max']]);

                if ($userRole === 'ppk') {
                    $query->where('pic', $userName);
                }

                $utilization[$level] = $query->count();
            }

            return $utilization;
        });
    }

    /**
     * Get alerts and upcoming deadlines with role-based filtering
     */
    private function getAlertsAndDeadlines($userRole, $userName)
    {
        return Cache::remember("alerts_deadlines_{$userRole}_{$userName}_" . date('Y-m-d'), 60, function () use ($userRole, $userName) {
            $alerts = collect();

            $query = BudgetCategory::active();
            if ($userRole === 'ppk') {
                $query->where('pic', $userName);
            }

            // Low realization alerts
            $lowRealization = $query->whereRaw('(total_penyerapan / NULLIF(budget_allocation, 0) * 100) < 25')
                ->where('budget_allocation', '>', 0)
                ->count();

            if ($lowRealization > 0) {
                $alerts->push([
                    'type' => 'warning',
                    'title' => 'Realisasi Rendah',
                    'message' => "{$lowRealization} kategori anggaran memiliki realisasi di bawah 25%",
                    'action_url' => route('budget.index') . '?low_realization=1',
                    'priority' => 'medium'
                ]);
            }

            // High outstanding bills
            $totalOutstanding = $query->sum('tagihan_outstanding') ?: 0;
            $highOutstanding = $query->where('tagihan_outstanding', '>', 0)->count();

            if ($highOutstanding > 0) {
                $alerts->push([
                    'type' => 'info',
                    'title' => 'Tagihan Outstanding',
                    'message' => "Rp " . number_format($totalOutstanding, 0, ',', '.') . " dalam {$highOutstanding} kategori anggaran",
                    'action_url' => route('bills.index') . '?status=pending',
                    'priority' => 'high'
                ]);
            }

            // Pending bills count
            $pendingQuery = Bill::where('status', 'pending');
            if ($userRole === 'ppk') {
                $pendingQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                    $q->where('pic', $userName);
                });
            }

            $pendingBills = $pendingQuery->count();
            if ($pendingBills > 5) { // Lower threshold for PPK users
                $alerts->push([
                    'type' => 'warning',
                    'title' => 'Banyak Tagihan Pending',
                    'message' => "{$pendingBills} tagihan menunggu persetujuan",
                    'action_url' => route('bills.index') . '?status=pending',
                    'priority' => 'high'
                ]);
            }

            return $alerts->sortByDesc('priority')->take(5)->values();
        });
    }

    /**
     * Get key performance indicators with role-based filtering
     */
    private function getKeyPerformanceIndicators($year, $userRole, $userName)
    {
        return Cache::remember("kpi_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($year, $userRole, $userName) {
            $budgetQuery = BudgetCategory::active();
            $billsQuery = Bill::where('year', $year);

            if ($userRole === 'ppk') {
                $budgetQuery->where('pic', $userName);
                $billsQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                    $q->where('pic', $userName);
                });
            }

            $totalBudget = $budgetQuery->sum('budget_allocation') ?: 0;
            $totalRealization = $budgetQuery->sum('total_penyerapan') ?: 0;
            $totalBills = $billsQuery->count();
            $approvedBills = (clone $billsQuery)->where('status', 'sp2d')->count();

            return [
                'budget_efficiency' => $totalBudget > 0 ? round(($totalRealization / $totalBudget) * 100, 2) : 0,
                'approval_rate' => $totalBills > 0 ? round(($approvedBills / $totalBills) * 100, 2) : 0,
                'avg_processing_time' => $this->getAverageProcessingTime($year, $userRole, $userName),
                'categories_on_track' => $this->getCategoriesOnTrack($userRole, $userName),
                'monthly_growth' => $this->getMonthlyGrowthRate($year, $userRole, $userName),
            ];
        });
    }

    /**
     * Get average processing time for bills with role-based filtering
     */
    private function getAverageProcessingTime($year, $userRole, $userName)
    {
        $query = Bill::where('year', $year)
            ->where('status', 'sp2d')
            ->whereNotNull('approved_at');

        if ($userRole === 'ppk') {
            $query->whereHas('budgetCategory', function ($q) use ($userName) {
                $q->where('pic', $userName);
            });
        }

        $avgTime = $query->selectRaw('AVG(EXTRACT(EPOCH FROM (approved_at - created_at))/3600) as avg_hours')
            ->first();

        return $avgTime && $avgTime->avg_hours ? round($avgTime->avg_hours, 1) : 0;
    }

    /**
     * Get categories that are on track (>50% realization) with role-based filtering
     */
    private function getCategoriesOnTrack($userRole, $userName)
    {
        $query = BudgetCategory::active();

        if ($userRole === 'ppk') {
            $query->where('pic', $userName);
        }

        $total = $query->count();
        if ($total == 0) return 0;

        $onTrackQuery = BudgetCategory::active()
            ->whereRaw('(total_penyerapan / NULLIF(budget_allocation, 0) * 100) >= 50');

        if ($userRole === 'ppk') {
            $onTrackQuery->where('pic', $userName);
        }

        $onTrack = $onTrackQuery->count();

        return round(($onTrack / $total) * 100, 2);
    }

    /**
     * Get monthly growth rate with role-based filtering
     */
    private function getMonthlyGrowthRate($year, $userRole, $userName)
    {
        $currentMonth = date('n');
        $previousMonth = $currentMonth > 1 ? $currentMonth - 1 : 12;

        $currentField = $this->getMonthFieldName($currentMonth);
        $previousField = $this->getMonthFieldName($previousMonth);

        $query = BudgetCategory::active();

        if ($userRole === 'ppk') {
            $query->where('pic', $userName);
        }

        $currentRealization = $query->sum($currentField) ?: 0;
        $previousRealization = $query->sum($previousField) ?: 0;

        if ($previousRealization > 0) {
            return round((($currentRealization - $previousRealization) / $previousRealization) * 100, 2);
        }

        return 0;
    }

    /**
     * Get month field name for database
     */
    private function getMonthFieldName($month)
    {
        $monthFields = [
            1 => 'realisasi_jan', 2 => 'realisasi_feb', 3 => 'realisasi_mar',
            4 => 'realisasi_apr', 5 => 'realisasi_mei', 6 => 'realisasi_jun',
            7 => 'realisasi_jul', 8 => 'realisasi_agu', 9 => 'realisasi_sep',
            10 => 'realisasi_okt', 11 => 'realisasi_nov', 12 => 'realisasi_des'
        ];

        return $monthFields[$month] ?? 'realisasi_jan';
    }

    /**
     * API endpoint for real-time data updates
     */
    public function getRealtimeData(Request $request)
    {
        $year = (int) $request->get('year', date('Y'));
        $user = Auth::user();
        $userRole = $user->role;
        $userName = $user->name;

        try {
            return response()->json([
                'summary' => $this->getDashboardData($year, $userRole, $userName),
                'monthly_realization' => $this->getMonthlyRealization($year, $userRole, $userName),
                'bills_status' => $this->getBillsStatusDistribution($year, $userRole, $userName),
                'alerts' => $this->getAlertsAndDeadlines($userRole, $userName),
                'kpi' => $this->getKeyPerformanceIndicators($year, $userRole, $userName),
                'last_updated' => now()->toISOString(),
                'user_role' => $userRole
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard realtime data error', [
                'error' => $e->getMessage(),
                'year' => $year,
                'user_id' => $user->id,
                'user_role' => $userRole
            ]);

            return response()->json([
                'error' => 'Failed to fetch data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
