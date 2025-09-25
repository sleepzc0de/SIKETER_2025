<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Check if user can view reports
            if (!Auth::user()->canViewReports() && !Auth::user()->isAdmin() && !Auth::user()->isPimpinan()) {
                abort(403, 'Anda tidak memiliki akses untuk melihat dashboard.');
            }

            // Initialize year and month with proper defaults
            $year = (int) ($request->get('year', date('Y')));
            $month = (int) ($request->get('month', date('n')));

            // Get user-specific data based on role
            $userRole = Auth::user()->role;
            $userName = Auth::user()->name;

            // Main dashboard data with role-based filtering
            $dashboardData = $this->getDashboardData($year, $userRole, $userName);

            // Monthly realization trend
            $monthlyRealization = $this->getMonthlyRealization($year, $userRole, $userName);

            // Top performing categories
            $topCategories = $this->getTopCategories($year, $userRole, $userName);

            // Bottom performing categories
            $bottomCategories = $this->getBottomCategories($year, $userRole, $userName);

            // Recent activities
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

        } catch (\Exception $e) {
            Log::error('Dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return view('dashboard', [
                'dashboardData' => $this->getEmptyDashboardData(),
                'monthlyRealization' => collect(),
                'topCategories' => collect(),
                'bottomCategories' => collect(),
                'recentActivities' => collect(),
                'billsStatus' => $this->getEmptyBillsStatus(),
                'monthlyBillsTrend' => collect(),
                'picPerformance' => collect(),
                'quarterlyComparison' => collect(),
                'budgetUtilization' => [],
                'alerts' => collect(),
                'kpi' => $this->getEmptyKPI(),
                'year' => date('Y'),
                'month' => date('n'),
                'availableYears' => range(2020, date('Y') + 1),
                'userPermissions' => []
            ])->with('error', 'Terjadi kesalahan saat memuat dashboard. Silakan coba lagi.');
        }
    }

    private function getAvailableYears()
    {
        try {
            $currentYear = date('Y');
            $years = range(2020, $currentYear + 1);

            // Get years from bills data if available
            $billYears = Bill::distinct()
                ->whereNotNull('year')
                ->pluck('year')
                ->filter()
                ->toArray();

            return collect($years)
                ->merge($billYears)
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting available years', ['error' => $e->getMessage()]);
            return range(2020, date('Y') + 1);
        }
    }

    private function getDashboardData($year, $userRole, $userName)
    {
        return Cache::remember("dashboard_data_{$year}_{$userRole}_{$userName}_" . date('Y-m-d-H'), 60, function () use ($year, $userRole, $userName) {
            try {
                $query = BudgetCategory::where('is_active', true);

                if ($userRole === 'ppk') {
                    $query->where('pic', $userName);
                }

                $totalBudget = $query->sum('budget_allocation') ?: 0;
                $totalRealization = $query->sum('total_penyerapan') ?: 0;
                $totalOutstanding = $query->sum('tagihan_outstanding') ?: 0;
                $remainingBudget = $totalBudget - $totalRealization;
                $realizationPercentage = $totalBudget > 0 ? ($totalRealization / $totalBudget) * 100 : 0;
                $outstandingPercentage = $totalBudget > 0 ? ($totalOutstanding / $totalBudget) * 100 : 0;
                $totalCategories = $query->count();

                return [
                    'totalBudget' => (float) $totalBudget,
                    'totalRealization' => (float) $totalRealization,
                    'totalOutstanding' => (float) $totalOutstanding,
                    'remainingBudget' => (float) $remainingBudget,
                    'realizationPercentage' => round($realizationPercentage, 2),
                    'outstandingPercentage' => round($outstandingPercentage, 2),
                    'realizationGrowth' => 0,
                    'totalCategories' => $totalCategories,
                    'avgRealizationPerCategory' => $totalCategories > 0 ? round($totalRealization / $totalCategories, 2) : 0,
                ];
            } catch (\Exception $e) {
                Log::error('Error getting dashboard data', ['error' => $e->getMessage()]);
                return $this->getEmptyDashboardData();
            }
        });
    }

    private function getMonthlyRealization($year, $userRole, $userName)
    {
        return Cache::remember("monthly_realization_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($year, $userRole, $userName) {
            try {
                $monthlyData = collect();

                for ($month = 1; $month <= 12; $month++) {
                    $monthName = Carbon::create()->month($month)->format('M');
                    $fieldName = $this->getMonthFieldName($month);

                    $query = BudgetCategory::where('is_active', true);
                    if ($userRole === 'ppk') {
                        $query->where('pic', $userName);
                    }

                    $realization = $query->sum($fieldName) ?: 0;

                    $billsQuery = Bill::where('month', $month)->where('year', $year);
                    if ($userRole === 'ppk') {
                        $billsQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                            $q->where('pic', $userName);
                        });
                    }

                    $billsCount = $billsQuery->count();
                    $sp2dCount = (clone $billsQuery)->where('status', 'Tagihan Telah SP2D')->count();

                    $monthlyData->push([
                        'month' => $month,
                        'month_name' => $monthName,
                        'realization' => (float) $realization,
                        'bills_count' => $billsCount,
                        'sp2d_count' => $sp2dCount,
                        'completion_rate' => $billsCount > 0 ? round(($sp2dCount / $billsCount) * 100, 2) : 0
                    ]);
                }

                return $monthlyData;
            } catch (\Exception $e) {
                Log::error('Error getting monthly realization', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getTopCategories($year, $userRole, $userName)
    {
        return Cache::remember("top_categories_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($userRole, $userName) {
            try {
                $query = BudgetCategory::where('is_active', true)
                    ->where('budget_allocation', '>', 0);

                if ($userRole === 'ppk') {
                    $query->where('pic', $userName);
                }

                return $query->orderByRaw('(total_penyerapan / NULLIF(budget_allocation, 0)) DESC')
                    ->take(5)
                    ->get()
                    ->map(function ($item) {
                        $percentage = $item->budget_allocation > 0 ? ($item->total_penyerapan / $item->budget_allocation) * 100 : 0;
                        return [
                            'id' => $item->id,
                            'full_code' => "{$item->kro_code}-{$item->ro_code}-{$item->account_code}",
                            'name' => $item->program_kegiatan_output,
                            'budget_allocation' => (float) $item->budget_allocation,
                            'realization' => (float) $item->total_penyerapan,
                            'percentage' => round($percentage, 2),
                            'pic' => $item->pic
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error getting top categories', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getBottomCategories($year, $userRole, $userName)
    {
        return Cache::remember("bottom_categories_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($userRole, $userName) {
            try {
                $query = BudgetCategory::where('is_active', true)
                    ->where('budget_allocation', '>', 0);

                if ($userRole === 'ppk') {
                    $query->where('pic', $userName);
                }

                return $query->orderByRaw('(total_penyerapan / NULLIF(budget_allocation, 0)) ASC')
                    ->take(5)
                    ->get()
                    ->map(function ($item) {
                        $percentage = $item->budget_allocation > 0 ? ($item->total_penyerapan / $item->budget_allocation) * 100 : 0;
                        return [
                            'id' => $item->id,
                            'full_code' => "{$item->kro_code}-{$item->ro_code}-{$item->account_code}",
                            'name' => $item->program_kegiatan_output,
                            'budget_allocation' => (float) $item->budget_allocation,
                            'realization' => (float) $item->total_penyerapan,
                            'percentage' => round($percentage, 2),
                            'pic' => $item->pic
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error getting bottom categories', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getRecentActivities($userRole, $userName)
    {
        return Cache::remember("recent_activities_{$userRole}_{$userName}_" . date('Y-m-d-H'), 30, function () use ($userRole, $userName) {
            try {
                $activities = collect();

                $billsQuery = Bill::with(['budgetCategory', 'creator']);
                if ($userRole === 'ppk') {
                    $billsQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                        $q->where('pic', $userName);
                    });
                }

                $recentBills = $billsQuery->orderBy('created_at', 'desc')->take(8)->get();

                foreach ($recentBills as $bill) {
                    $activities->push([
                        'type' => 'bill_created',
                        'title' => "Tagihan {$bill->bill_number} dibuat",
                        'description' => $bill->budgetCategory ? $bill->budgetCategory->program_kegiatan_output : 'N/A',
                        'amount' => (float) ($bill->amount ?: 0),
                        'user' => $bill->creator ? $bill->creator->name : 'System',
                        'time' => $bill->created_at->toISOString(),
                        'status' => $bill->status,
                        'url' => route('bills.show', $bill->id)
                    ]);
                }

                // Recent approvals
                $approvalsQuery = Bill::with(['budgetCategory'])
                    ->where('updated_at', '>', Carbon::now()->subDays(7))
                    ->where('status', 'Tagihan Telah SP2D');

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
                        'amount' => (float) ($bill->amount ?: 0),
                        'user' => 'System',
                        'time' => $bill->updated_at->toISOString(),
                        'status' => $bill->status,
                        'url' => route('bills.show', $bill->id)
                    ]);
                }

                return $activities->sortByDesc('time')->take(10)->values();
            } catch (\Exception $e) {
                Log::error('Error getting recent activities', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getBillsStatusDistribution($year, $userRole, $userName)
    {
        return Cache::remember("bills_status_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 60, function () use ($year, $userRole, $userName) {
            try {
                $query = Bill::where('year', $year);
                if ($userRole === 'ppk') {
                    $query->whereHas('budgetCategory', function ($q) use ($userName) {
                        $q->where('pic', $userName);
                    });
                }

                $statusData = $query->select('status',
                    DB::raw('count(*) as count'),
                    DB::raw('COALESCE(sum(amount), 0) as total_amount'))
                    ->groupBy('status')
                    ->get();

                $result = [
                    'pending' => ['count' => 0, 'amount' => 0, 'color' => '#f59e0b'],
                    'sp2d' => ['count' => 0, 'amount' => 0, 'color' => '#10b981'],
                    'cancelled' => ['count' => 0, 'amount' => 0, 'color' => '#ef4444']
                ];

                foreach ($statusData as $data) {
                    if ($data->status === 'Tagihan Telah SP2D') {
                        $result['sp2d']['count'] += $data->count;
                        $result['sp2d']['amount'] += (float) $data->total_amount;
                    } elseif ($data->status === 'cancelled') {
                        $result['cancelled']['count'] += $data->count;
                        $result['cancelled']['amount'] += (float) $data->total_amount;
                    } else {
                        $result['pending']['count'] += $data->count;
                        $result['pending']['amount'] += (float) $data->total_amount;
                    }
                }

                return $result;
            } catch (\Exception $e) {
                Log::error('Error getting bills status distribution', ['error' => $e->getMessage()]);
                return $this->getEmptyBillsStatus();
            }
        });
    }

    private function getMonthlyBillsTrend($year, $userRole, $userName)
    {
        return Cache::remember("monthly_bills_trend_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($year, $userRole, $userName) {
            try {
                $billsTrend = collect();

                for ($month = 1; $month <= 12; $month++) {
                    $monthBills = Bill::where('year', $year)->where('month', $month);

                    if ($userRole === 'ppk') {
                        $monthBills->whereHas('budgetCategory', function ($q) use ($userName) {
                            $q->where('pic', $userName);
                        });
                    }

                    $total = $monthBills->count();
                    $sp2d = (clone $monthBills)->where('status', 'Tagihan Telah SP2D')->count();
                    $cancelled = (clone $monthBills)->where('status', 'cancelled')->count();
                    $pending = $total - $sp2d - $cancelled;
                    $totalAmount = (clone $monthBills)->sum('amount') ?: 0;

                    $billsTrend->push([
                        'month' => $month,
                        'month_name' => Carbon::create()->month($month)->format('M'),
                        'total' => $total,
                        'pending' => $pending,
                        'sp2d' => $sp2d,
                        'cancelled' => $cancelled,
                        'total_amount' => (float) $totalAmount,
                    ]);
                }

                return $billsTrend;
            } catch (\Exception $e) {
                Log::error('Error getting monthly bills trend', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getPICPerformance($year, $userRole)
    {
        if (!in_array($userRole, ['admin', 'pimpinan'])) {
            return collect();
        }

        return Cache::remember("pic_performance_{$year}_" . date('Y-m-d'), 180, function () {
            try {
                return BudgetCategory::where('is_active', true)
                    ->select('pic')
                    ->selectRaw('COUNT(*) as categories_count')
                    ->selectRaw('COALESCE(SUM(budget_allocation), 0) as total_budget')
                    ->selectRaw('COALESCE(SUM(total_penyerapan), 0) as total_realization')
                    ->groupBy('pic')
                    ->orderByDesc('total_realization')
                    ->take(10)
                    ->get()
                    ->map(function ($item) {
                        $efficiency_score = $item->total_budget > 0
                            ? round(($item->total_realization / $item->total_budget) * 100, 2)
                            : 0;

                        return [
                            'pic' => $item->pic,
                            'categories_count' => $item->categories_count,
                            'total_budget' => (float) $item->total_budget,
                            'total_realization' => (float) $item->total_realization,
                            'efficiency_score' => $efficiency_score
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error getting PIC performance', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getQuarterlyComparison($year, $userRole, $userName)
    {
        return Cache::remember("quarterly_comparison_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 180, function () use ($year, $userRole, $userName) {
            try {
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
                        $query = BudgetCategory::where('is_active', true);
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
                    $sp2dCount = (clone $billsQuery)->where('status', 'Tagihan Telah SP2D')->count();

                    $quarterlyData->push([
                        'quarter' => $quarter,
                        'realization' => (float) $realization,
                        'bills_count' => $billsCount,
                        'sp2d_count' => $sp2dCount,
                        'completion_rate' => $billsCount > 0 ? round(($sp2dCount / $billsCount) * 100, 2) : 0
                    ]);
                }

                return $quarterlyData;
            } catch (\Exception $e) {
                Log::error('Error getting quarterly comparison', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getBudgetUtilization($year, $userRole, $userName)
    {
        return Cache::remember("budget_utilization_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 180, function () use ($userRole, $userName) {
            try {
                $utilizationRanges = [
                    'excellent' => ['min' => 80, 'max' => 100],
                    'good' => ['min' => 60, 'max' => 79],
                    'fair' => ['min' => 40, 'max' => 59],
                    'poor' => ['min' => 0, 'max' => 39]
                ];

                $utilization = [];

                foreach ($utilizationRanges as $level => $range) {
                    $query = BudgetCategory::where('is_active', true)
                        ->whereRaw('(total_penyerapan / NULLIF(budget_allocation, 0) * 100) BETWEEN ? AND ?',
                            [$range['min'], $range['max']]);

                    if ($userRole === 'ppk') {
                        $query->where('pic', $userName);
                    }

                    $utilization[$level] = $query->count();
                }

                return $utilization;
            } catch (\Exception $e) {
                Log::error('Error getting budget utilization', ['error' => $e->getMessage()]);
                return ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0];
            }
        });
    }

    private function getAlertsAndDeadlines($userRole, $userName)
    {
        return Cache::remember("alerts_deadlines_{$userRole}_{$userName}_" . date('Y-m-d'), 60, function () use ($userRole, $userName) {
            try {
                $alerts = collect();

                $query = BudgetCategory::where('is_active', true);
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
                $pendingQuery = Bill::whereNotIn('status', ['Tagihan Telah SP2D', 'cancelled']);
                if ($userRole === 'ppk') {
                    $pendingQuery->whereHas('budgetCategory', function ($q) use ($userName) {
                        $q->where('pic', $userName);
                    });
                }

                $pendingBills = $pendingQuery->count();
                $threshold = $userRole === 'ppk' ? 3 : 10;

                if ($pendingBills > $threshold) {
                    $alerts->push([
                        'type' => 'warning',
                        'title' => 'Banyak Tagihan Pending',
                        'message' => "{$pendingBills} tagihan menunggu persetujuan",
                        'action_url' => route('bills.index') . '?status=pending',
                        'priority' => 'high'
                    ]);
                }

                return $alerts->sortByDesc('priority')->take(5)->values();
            } catch (\Exception $e) {
                Log::error('Error getting alerts and deadlines', ['error' => $e->getMessage()]);
                return collect();
            }
        });
    }

    private function getKeyPerformanceIndicators($year, $userRole, $userName)
    {
        return Cache::remember("kpi_{$year}_{$userRole}_{$userName}_" . date('Y-m-d'), 120, function () use ($year, $userRole, $userName) {
            try {
                $budgetQuery = BudgetCategory::where('is_active', true);
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
                $approvedBills = (clone $billsQuery)->where('status', 'Tagihan Telah SP2D')->count();

                return [
                    'budget_efficiency' => $totalBudget > 0 ? round(($totalRealization / $totalBudget) * 100, 2) : 0,
                    'approval_rate' => $totalBills > 0 ? round(($approvedBills / $totalBills) * 100, 2) : 0,
                    'avg_processing_time' => $this->getAverageProcessingTime($year, $userRole, $userName),
                    'categories_on_track' => $this->getCategoriesOnTrack($userRole, $userName),
                    'monthly_growth' => $this->getMonthlyGrowthRate($year, $userRole, $userName),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting KPI', ['error' => $e->getMessage()]);
                return $this->getEmptyKPI();
            }
        });
    }

    private function getAverageProcessingTime($year, $userRole, $userName)
    {
        try {
            $query = Bill::where('year', $year)
                ->where('status', 'Tagihan Telah SP2D')
                ->whereNotNull('approved_at')
                ->whereNotNull('created_at');

            if ($userRole === 'ppk') {
                $query->whereHas('budgetCategory', function ($q) use ($userName) {
                    $q->where('pic', $userName);
                });
            }

            $result = $query->selectRaw('AVG(EXTRACT(EPOCH FROM (approved_at - created_at))/3600) as avg_hours')
                ->first();

            if ($result && $result->avg_hours) {
                return round($result->avg_hours, 1);
            }

            // Fallback calculation using tgl_sp2d
            return $this->getFallbackProcessingTime($year, $userRole, $userName);
        } catch (\Exception $e) {
            Log::error('Error calculating processing time', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getFallbackProcessingTime($year, $userRole, $userName)
    {
        try {
            $query = Bill::where('year', $year)
                ->where('status', 'Tagihan Telah SP2D')
                ->whereNotNull('tgl_sp2d')
                ->whereNotNull('created_at');

            if ($userRole === 'ppk') {
                $query->whereHas('budgetCategory', function ($q) use ($userName) {
                    $q->where('pic', $userName);
                });
            }

            $bills = $query->get();

            if ($bills->count() === 0) {
                return 0;
            }

            $totalHours = 0;
            $count = 0;

            foreach ($bills as $bill) {
                if ($bill->created_at && $bill->tgl_sp2d) {
                    $createdAt = Carbon::parse($bill->created_at);
                    $sp2dDate = Carbon::parse($bill->tgl_sp2d);
                    $diffInHours = $createdAt->diffInHours($sp2dDate);
                    $totalHours += $diffInHours;
                    $count++;
                }
            }

            return $count > 0 ? round($totalHours / $count, 1) : 0;
        } catch (\Exception $e) {
            Log::error('Error in fallback processing time', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getCategoriesOnTrack($userRole, $userName)
    {
        try {
            $query = BudgetCategory::where('is_active', true);
            if ($userRole === 'ppk') {
                $query->where('pic', $userName);
            }

            $total = $query->count();
            if ($total == 0) return 0;

            $onTrackQuery = BudgetCategory::where('is_active', true)
                ->whereRaw('(total_penyerapan / NULLIF(budget_allocation, 0) * 100) >= 50');

            if ($userRole === 'ppk') {
                $onTrackQuery->where('pic', $userName);
            }

            $onTrack = $onTrackQuery->count();
            return round(($onTrack / $total) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error getting categories on track', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getMonthlyGrowthRate($year, $userRole, $userName)
    {
        try {
            $currentMonth = date('n');
            $previousMonth = $currentMonth > 1 ? $currentMonth - 1 : 12;

            $currentField = $this->getMonthFieldName($currentMonth);
            $previousField = $this->getMonthFieldName($previousMonth);

            $query = BudgetCategory::where('is_active', true);
            if ($userRole === 'ppk') {
                $query->where('pic', $userName);
            }

            $currentRealization = $query->sum($currentField) ?: 0;
            $previousRealization = $query->sum($previousField) ?: 0;

            if ($previousRealization > 0) {
                return round((($currentRealization - $previousRealization) / $previousRealization) * 100, 2);
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error getting monthly growth rate', ['error' => $e->getMessage()]);
            return 0;
        }
    }

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

    // Helper methods for empty data
    private function getEmptyDashboardData()
    {
        return [
            'totalBudget' => 0,
            'totalRealization' => 0,
            'totalOutstanding' => 0,
            'remainingBudget' => 0,
            'realizationPercentage' => 0,
            'outstandingPercentage' => 0,
            'realizationGrowth' => 0,
            'totalCategories' => 0,
            'avgRealizationPerCategory' => 0,
        ];
    }

    private function getEmptyBillsStatus()
    {
        return [
            'pending' => ['count' => 0, 'amount' => 0, 'color' => '#f59e0b'],
            'sp2d' => ['count' => 0, 'amount' => 0, 'color' => '#10b981'],
            'cancelled' => ['count' => 0, 'amount' => 0, 'color' => '#ef4444']
        ];
    }

    private function getEmptyKPI()
    {
        return [
            'budget_efficiency' => 0,
            'approval_rate' => 0,
            'avg_processing_time' => 0,
            'categories_on_track' => 0,
            'monthly_growth' => 0,
        ];
    }

    public function getRealtimeData(Request $request)
    {
        try {
            $year = (int) $request->get('year', date('Y'));
            $user = Auth::user();
            $userRole = $user->role;
            $userName = $user->name;

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
            Log::error('Dashboard realtime data error', [
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
