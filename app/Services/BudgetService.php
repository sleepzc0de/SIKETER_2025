<?php

namespace App\Services;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BudgetService
{
    /**
     * Check if budget can be safely deleted
     */
    public function canDelete(BudgetCategory $budget): array
    {
        $activeBills = $budget->bills()->where('status', '!=', 'cancelled')->count();
        $pendingBills = $budget->bills()->where('status', 'pending')->count();
        $sp2dBills = $budget->bills()->where('status', 'sp2d')->count();
        $cancelledBills = $budget->bills()->where('status', 'cancelled')->count();

        $canDelete = $activeBills === 0;
        $warnings = [];

        if ($pendingBills > 0) {
            $warnings[] = "Terdapat {$pendingBills} tagihan pending";
        }

        if ($sp2dBills > 0) {
            $warnings[] = "Terdapat {$sp2dBills} tagihan yang sudah SP2D";
        }

        if ($budget->total_penyerapan > 0) {
            $warnings[] = "Anggaran memiliki realisasi sebesar Rp " . number_format($budget->total_penyerapan, 0, ',', '.');
        }

        return [
            'can_delete' => $canDelete,
            'warnings' => $warnings,
            'bills_summary' => [
                'total' => $budget->bills()->count(),
                'active' => $activeBills,
                'pending' => $pendingBills,
                'sp2d' => $sp2dBills,
                'cancelled' => $cancelledBills
            ]
        ];
    }

    /**
     * Delete budget with proper cleanup
     */
    public function delete(BudgetCategory $budget): array
    {
        $deleteInfo = $this->canDelete($budget);

        if (!$deleteInfo['can_delete']) {
            throw new \Exception('Budget cannot be deleted due to active bills');
        }

        $billsDeleted = 0;

        DB::transaction(function () use ($budget, &$billsDeleted) {
            // Delete cancelled bills
            $billsDeleted = $budget->bills()->where('status', 'cancelled')->count();
            $budget->bills()->where('status', 'cancelled')->delete();

            // Delete related budget realizations
            $budget->budgetRealizations()->delete();

            // Clear cache
            $this->clearBudgetCache($budget);

            // Delete budget
            $budget->delete();
        });

        // Log deletion
        Log::info('Budget deleted', [
            'budget_id' => $budget->id,
            'budget_code' => $budget->full_code,
            'bills_deleted' => $billsDeleted,
            'deleted_at' => now()
        ]);

        return [
            'success' => true,
            'bills_deleted' => $billsDeleted,
            'message' => 'Data anggaran berhasil dihapus.' . ($billsDeleted > 0 ? " Termasuk {$billsDeleted} tagihan yang dibatalkan." : '')
        ];
    }

    /**
     * Bulk delete budgets
     */
    public function bulkDelete(array $budgetIds): array
    {
        $budgets = BudgetCategory::whereIn('id', $budgetIds)->get();

        // Check all budgets can be deleted
        $budgetsWithActiveBills = $budgets->filter(function ($budget) {
            return $budget->bills()->where('status', '!=', 'cancelled')->count() > 0;
        });

        if ($budgetsWithActiveBills->count() > 0) {
            $codes = $budgetsWithActiveBills->pluck('full_code')->join(', ');
            throw new \Exception("Cannot delete some budgets with active bills: {$codes}");
        }

        $deletedCount = 0;
        $totalBillsDeleted = 0;

        DB::transaction(function () use ($budgets, &$deletedCount, &$totalBillsDeleted) {
            foreach ($budgets as $budget) {
                $billsCount = $budget->bills()->where('status', 'cancelled')->count();
                $budget->bills()->where('status', 'cancelled')->delete();
                $totalBillsDeleted += $billsCount;

                $budget->budgetRealizations()->delete();
                $this->clearBudgetCache($budget);
                $budget->delete();
                $deletedCount++;
            }
        });

        // Log bulk deletion
        Log::info('Bulk budget deletion', [
            'deleted_count' => $deletedCount,
            'budget_ids' => $budgetIds,
            'total_bills_deleted' => $totalBillsDeleted,
            'deleted_at' => now()
        ]);

        return [
            'success' => true,
            'deleted_count' => $deletedCount,
            'bills_deleted' => $totalBillsDeleted,
            'message' => "Berhasil menghapus {$deletedCount} data anggaran." . ($totalBillsDeleted > 0 ? " Termasuk {$totalBillsDeleted} tagihan yang dibatalkan." : '')
        ];
    }

    /**
     * Clear budget related cache
     */
    private function clearBudgetCache(BudgetCategory $budget): void
    {
        try {
            $cacheKeys = [
                "budget_{$budget->id}_total_realization",
                "budget_{$budget->id}_realization_percentage",
                "budget_{$budget->id}_remaining_budget",
                'budget_categories_for_bills',
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Clear tag-based cache
            Cache::tags(['budget_stats'])->flush();

        } catch (\Exception $e) {
            Log::warning('Failed to clear budget cache', [
                'budget_id' => $budget->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
