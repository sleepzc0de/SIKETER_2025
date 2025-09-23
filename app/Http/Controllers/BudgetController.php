<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use App\Models\BudgetRealization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetExport;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = BudgetCategory::query();

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

        return view('budget.index', compact('budgets', 'pics', 'kegiatans'));
    }

    public function show($id)
    {
        try {
            $budget = BudgetCategory::findOrFail($id);
            $budget->load(['bills.creator', 'bills.approver']);

            $monthlyData = collect([
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

            return view('budget.show', compact('budget', 'monthlyData'));
        } catch (\Exception $e) {
            Log::error('Budget show error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('budget.index')->with('error', 'Data anggaran tidak ditemukan.');
        }
    }

    public function create()
    {
        // Check permission
        if (!Auth::user()->canManageBudget()) {
            return redirect()->route('budget.index')->with('error', 'Anda tidak memiliki akses untuk menambah data anggaran.');
        }

        return view('budget.create');
    }

    public function store(Request $request)
    {
        try {
            // Check permission
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')->with('error', 'Anda tidak memiliki akses untuk menambah data anggaran.');
            }

            $validated = $request->validate([
                'kegiatan' => 'required|string|max:255',
                'kro_code' => 'required|string|max:255',
                'ro_code' => 'required|string|max:255',
                'initial_code' => 'required|string|max:255',
                'account_code' => 'required|string|max:255',
                'program_kegiatan_output' => 'required|string',
                'pic' => 'required|string|max:255',
                'budget_allocation' => 'required|numeric|min:0',
                'reference' => 'required|string',
                'reference2' => 'nullable|string',
                'reference_output' => 'nullable|string',
                'length' => 'required|integer|min:0',
            ]);

            // Ensure auto-generated fields are correctly calculated
            $validated['reference'] = $validated['kegiatan'] . $validated['kro_code'] .
                $validated['ro_code'] . $validated['initial_code'] .
                $validated['account_code'];
            $validated['reference2'] = $validated['kegiatan'] . $validated['kro_code'] .
                $validated['ro_code'] . $validated['initial_code'];
            $validated['reference_output'] = $validated['kegiatan'] . $validated['kro_code'] .
                $validated['ro_code'];
            $validated['length'] = strlen($validated['reference']);

            // Calculate initial values
            $validated['sisa_anggaran'] = $validated['budget_allocation'];
            $validated['total_penyerapan'] = 0;
            $validated['tagihan_outstanding'] = 0;
            $validated['is_active'] = true;

            // Initialize monthly realization to 0
            $monthlyFields = [
                'realisasi_jan',
                'realisasi_feb',
                'realisasi_mar',
                'realisasi_apr',
                'realisasi_mei',
                'realisasi_jun',
                'realisasi_jul',
                'realisasi_agu',
                'realisasi_sep',
                'realisasi_okt',
                'realisasi_nov',
                'realisasi_des'
            ];

            foreach ($monthlyFields as $field) {
                $validated[$field] = 0;
            }

            $budget = BudgetCategory::create($validated);

            Log::info('Budget created with auto-generated fields', [
                'budget_id' => $budget->id,
                'created_by' => Auth::id(),
                'reference' => $validated['reference'],
                'reference2' => $validated['reference2'],
                'reference_output' => $validated['reference_output'],
                'length' => $validated['length'],
                'data' => $validated
            ]);

            return redirect()->route('budget.show', $budget->id)->with('success', 'Data anggaran berhasil ditambahkan dengan referensi yang digenerate otomatis.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Budget store error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Gagal menyimpan data anggaran. Silakan coba lagi.')->withInput();
        }
    }

    public function edit($id)
    {
        try {
            // Check permission
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data anggaran.');
            }

            $budget = BudgetCategory::findOrFail($id);

            // Log for debugging
            Log::info('Budget edit accessed', [
                'budget_id' => $budget->id,
                'budget_data' => $budget->toArray(),
                'user_id' => Auth::id()
            ]);

            return view('budget.edit', compact('budget'));
        } catch (\Exception $e) {
            Log::error('Budget edit error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('budget.index')->with('error', 'Data anggaran tidak ditemukan.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Check permission
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data anggaran.');
            }

            $budget = BudgetCategory::findOrFail($id);

            // Log original data for debugging
            Log::info('Budget update attempt', [
                'budget_id' => $budget->id,
                'original_data' => $budget->toArray(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            $validated = $request->validate([
                'kegiatan' => 'required|string|max:255',
                'kro_code' => 'required|string|max:255',
                'ro_code' => 'required|string|max:255',
                'initial_code' => 'required|string|max:255',
                'account_code' => 'required|string|max:255',
                'program_kegiatan_output' => 'required|string',
                'pic' => 'required|string|max:255',
                'budget_allocation' => 'required|numeric|min:0',
                // Use hidden field values for auto-generated fields
                'reference_hidden' => 'required|string',
                'reference2_hidden' => 'required|string',
                'reference_output_hidden' => 'required|string',
                'length_hidden' => 'required|integer|min:0',
            ]);

            // Map hidden field values to actual field names
            $validated['reference'] = $validated['reference_hidden'];
            $validated['reference2'] = $validated['reference2_hidden'];
            $validated['reference_output'] = $validated['reference_output_hidden'];
            $validated['length'] = $validated['length_hidden'];

            // Remove hidden field keys
            unset(
                $validated['reference_hidden'],
                $validated['reference2_hidden'],
                $validated['reference_output_hidden'],
                $validated['length_hidden']
            );

            // Double-check auto-generated values for consistency
            $expectedReference = $validated['kegiatan'] . $validated['kro_code'] .
                $validated['ro_code'] . $validated['initial_code'] .
                $validated['account_code'];
            $expectedReference2 = $validated['kegiatan'] . $validated['kro_code'] .
                $validated['ro_code'] . $validated['initial_code'];
            $expectedReferenceOutput = $validated['kegiatan'] . $validated['kro_code'] .
                $validated['ro_code'];
            $expectedLength = strlen($expectedReference);

            // Override with calculated values to ensure consistency
            $validated['reference'] = $expectedReference;
            $validated['reference2'] = $expectedReference2;
            $validated['reference_output'] = $expectedReferenceOutput;
            $validated['length'] = $expectedLength;

            Log::info('Auto-generated field validation', [
                'expected_reference' => $expectedReference,
                'expected_reference2' => $expectedReference2,
                'expected_reference_output' => $expectedReferenceOutput,
                'expected_length' => $expectedLength,
                'submitted_reference' => $request->input('reference_hidden'),
                'submitted_reference2' => $request->input('reference2_hidden'),
                'submitted_reference_output' => $request->input('reference_output_hidden'),
                'submitted_length' => $request->input('length_hidden'),
            ]);

            // Store original budget allocation for comparison
            $originalBudgetAllocation = $budget->budget_allocation;

            // Update the budget using mass assignment
            $updated = $budget->update($validated);

            if (!$updated) {
                throw new \Exception('Failed to update budget in database');
            }

            // Refresh the model to get updated data
            $budget->refresh();

            // Recalculate sisa_anggaran if budget_allocation changed
            if ($originalBudgetAllocation != $budget->budget_allocation) {
                $budget->sisa_anggaran = $budget->budget_allocation - $budget->total_penyerapan;
                $budget->save();
            }

            // Update realization data to ensure consistency
            $budget->updateRealization();

            // Clear relevant caches
            Cache::forget("budget_{$budget->id}_total_realization");
            Cache::forget("budget_{$budget->id}_realization_percentage");
            Cache::forget("budget_{$budget->id}_remaining_budget");
            Cache::tags(['budget_stats'])->flush();

            Log::info('Budget updated successfully', [
                'budget_id' => $budget->id,
                'updated_data' => $budget->fresh()->toArray(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('budget.show', $budget->id)->with('success', 'Data anggaran berhasil diperbarui. Field referensi telah dihitung otomatis.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Budget update validation failed', [
                'budget_id' => $id,
                'validation_errors' => $e->validator->errors(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Budget update error', [
                'budget_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Gagal memperbarui data anggaran: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $budget = BudgetCategory::findOrFail($id);

            // Authorization check
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')->with('error', 'Anda tidak memiliki akses untuk menghapus data anggaran.');
            }

            // Check if budget has related bills
            $activeBillsCount = $budget->bills()->where('status', '!=', 'cancelled')->count();

            if ($activeBillsCount > 0) {
                return redirect()->route('budget.show', $budget->id)->with(
                    'error',
                    "Tidak dapat menghapus data anggaran. Masih terdapat {$activeBillsCount} tagihan aktif yang terkait dengan anggaran ini."
                );
            }

            // Perform deletion with transaction
            DB::transaction(function () use ($budget) {
                // Delete related cancelled bills if any
                $budget->bills()->where('status', 'cancelled')->delete();

                // Delete related budget realizations if any
                $budget->budgetRealizations()->delete();

                // Clear related cache
                $this->clearBudgetCache($budget);

                // Delete the budget category
                $budget->delete();
            });

            Log::info('Budget category deleted', [
                'id' => $budget->id,
                'code' => $budget->full_code,
                'deleted_by' => Auth::user()->id,
                'deleted_at' => now()
            ]);

            return redirect()->route('budget.index')->with('success', 'Data anggaran berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Budget deletion failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->id
            ]);

            return redirect()->route('budget.index')->with('error', 'Gagal menghapus data anggaran. Silakan coba lagi.');
        }
    }

    // Additional methods...
    public function export(Request $request)
    {
        return Excel::download(new BudgetExport($request->all()), 'budget-data-' . date('Y-m-d') . '.xlsx');
    }

    public function realizations(Request $request)
    {
        $query = BudgetCategory::with(['bills' => function ($q) {
            $q->where('status', 'sp2d');
        }]);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('pic')) {
            $query->byPIC($request->pic);
        }

        $year = $request->get('year', date('Y'));

        $budgets = $query->paginate(20);
        $pics = BudgetCategory::distinct()->pluck('pic');

        return view('budget.realizations', compact('budgets', 'pics', 'year'));
    }

    public function realizationDetail($id)
    {
        $budget = BudgetCategory::with(['bills' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('budget.realization-detail', compact('budget'));
    }

    /**
     * Clear budget-related cache
     */
    private function clearBudgetCache($budget)
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

            Cache::tags(['budget_stats'])->flush();
        } catch (\Exception $e) {
            Log::warning('Failed to clear budget cache', [
                'budget_id' => $budget->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
