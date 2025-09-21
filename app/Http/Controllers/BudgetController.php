<?php
// app/Http/Controllers/BudgetController.php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetExport;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'budget_index_' . md5(serialize($request->all()));

        $data = Cache::remember($cacheKey, 1800, function () use ($request) {
            $query = BudgetCategory::with(['bills' => function ($q) {
                $q->select('budget_category_id', DB::raw('SUM(amount) as total_amount'), 'status')
                  ->groupBy('budget_category_id', 'status');
            }]);

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('pic')) {
                $query->byPIC($request->pic);
            }

            if ($request->filled('kegiatan')) {
                $query->where('kegiatan', $request->kegiatan);
            }

            $budgets = $query->paginate(20);
            $pics = BudgetCategory::distinct()->pluck('pic');
            $kegiatans = BudgetCategory::distinct()->pluck('kegiatan');

            return compact('budgets', 'pics', 'kegiatans');
        });

        return view('budget.index', $data);
    }

    public function show(BudgetCategory $budget)
    {
        $budget->load(['bills.creator', 'bills.approver']);

        $monthlyData = Cache::remember("budget_{$budget->id}_monthly_chart", 1800, function () use ($budget) {
            return collect([
                ['month' => 1, 'total' => $budget->realisasi_jan],
                ['month' => 2, 'total' => $budget->realisasi_feb],
                ['month' => 3, 'total' => $budget->realisasi_mar],
                ['month' => 4, 'total' => $budget->realisasi_apr],
                ['month' => 5, 'total' => $budget->realisasi_mei],
                ['month' => 6, 'total' => $budget->realisasi_jun],
                ['month' => 7, 'total' => $budget->realisasi_jul],
                ['month' => 8, 'total' => $budget->realisasi_agu],
                ['month' => 9, 'total' => $budget->realisasi_sep],
                ['month' => 10, 'total' => $budget->realisasi_okt],
                ['month' => 11, 'total' => $budget->realisasi_nov],
                ['month' => 12, 'total' => $budget->realisasi_des],
            ]);
        });

        return view('budget.show', compact('budget', 'monthlyData'));
    }

    public function create()
    {
        return view('budget.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kegiatan' => 'required|string|max:255',
            'kro_code' => 'required|string|max:255',
            'ro_code' => 'required|string|max:255',
            'initial_code' => 'required|string|max:255',
            'account_code' => 'required|string|max:255',
            'program_kegiatan_output' => 'required|string',
            'pic' => 'required|string|max:255',
            'budget_allocation' => 'required|numeric|min:0',
            'reference' => 'required|string|max:255',
            'reference2' => 'nullable|string|max:255',
            'reference_output' => 'nullable|string|max:255',
            'length' => 'required|integer|min:1',
        ]);

        // Calculate sisa_anggaran initially
        $validated['sisa_anggaran'] = $validated['budget_allocation'];

        $budget = BudgetCategory::create($validated);

        // Clear cache
        Cache::tags(['budget_stats'])->flush();

        return redirect()->route('budget.index')->with('success', 'Data anggaran berhasil ditambahkan.');
    }

    public function edit(BudgetCategory $budget)
    {
        return view('budget.edit', compact('budget'));
    }

    public function update(Request $request, BudgetCategory $budget)
    {
        $validated = $request->validate([
            'kegiatan' => 'required|string|max:255',
            'kro_code' => 'required|string|max:255',
            'ro_code' => 'required|string|max:255',
            'initial_code' => 'required|string|max:255',
            'account_code' => 'required|string|max:255',
            'program_kegiatan_output' => 'required|string',
            'pic' => 'required|string|max:255',
            'budget_allocation' => 'required|numeric|min:0',
            'reference' => 'required|string|max:255',
            'reference2' => 'nullable|string|max:255',
            'reference_output' => 'nullable|string|max:255',
            'length' => 'required|integer|min:1',
        ]);

        $budget->update($validated);
        $budget->updateRealization(); // Recalculate sisa_anggaran

        // Clear cache
        Cache::tags(['budget_stats'])->flush();
        Cache::forget("budget_{$budget->id}_total_realization");
        Cache::forget("budget_{$budget->id}_realization_percentage");
        Cache::forget("budget_{$budget->id}_remaining_budget");

        return redirect()->route('budget.show', $budget)->with('success', 'Data anggaran berhasil diperbarui.');
    }

    public function destroy(BudgetCategory $budget)
    {
        try {
            DB::transaction(function () use ($budget) {
                // Delete related bills first
                $budget->bills()->delete();
                $budget->delete();
            });

            // Clear cache
            Cache::tags(['budget_stats'])->flush();

            return redirect()->route('budget.index')->with('success', 'Data anggaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('budget.index')->with('error', 'Gagal menghapus data anggaran.');
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new BudgetExport($request->all()), 'budget-data-' . date('Y-m-d') . '.xlsx');
    }
}
