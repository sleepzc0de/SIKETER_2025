<?php
// app/Http/Controllers/BudgetController.php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BudgetExport;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        try {
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

            if ($request->filled('year')) {
                $query->byYear($request->year);
            }

            $budgets = $query->orderBy('program_kegiatan_output')->paginate(20);
            $pics = BudgetCategory::distinct()->pluck('pic')->filter()->sort();
            $kegiatans = BudgetCategory::distinct()->pluck('kegiatan')->filter()->sort();
            $years = range(date('Y'), 2020);

            return view('budget.index', compact('budgets', 'pics', 'kegiatans', 'years'));
        } catch (\Exception $e) {
            Log::error('Budget index error: ' . $e->getMessage());
            return view('budget.index', [
                'budgets' => collect(),
                'pics' => collect(),
                'kegiatans' => collect(),
                'years' => range(date('Y'), 2020)
            ])->with('error', 'Terjadi kesalahan saat memuat data anggaran.');
        }
    }

    public function realizations(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $search = $request->get('search');
            $pic = $request->get('pic');

            // Base query
            $query = BudgetCategory::byYear($year);

            // Apply filters
            if ($search) {
                $query->search($search);
            }

            if ($pic) {
                $query->byPic($pic);
            }

            // Get budgets with calculated realization
            $budgets = $query->orderBy('program_kegiatan_output')
                ->paginate(15)
                ->withQueryString();

            // Load bills data for each budget to calculate realization
            $budgets->getCollection()->transform(function ($budget) {
                // Ensure we have the full_code
                if (!$budget->reference) {
                    $budget->reference = $budget->kegiatan . $budget->kro_code . $budget->ro_code . $budget->initial_code . $budget->account_code;
                }

                // Get bills yang sesuai dengan COA
                $sp2dBills = Bill::where('coa', $budget->reference)
                    ->where('status', 'Tagihan Telah SP2D')
                    ->sum('amount') ?: 0;

                $outstandingBills = Bill::where('coa', $budget->reference)
                    ->whereIn('status', [
                        'Kegiatan Masih Berlangsung',
                        'SPP Sedang Diproses',
                        'SPP Sudah Diserahkan ke KPPN'
                    ])
                    ->sum('amount') ?: 0;

                $budget->total_penyerapan = $sp2dBills;
                $budget->tagihan_outstanding = $outstandingBills;

                if (($budget->budget_allocation ?: 0) > 0) {
                    $budget->realization_percentage = ($budget->total_penyerapan / $budget->budget_allocation) * 100;
                } else {
                    $budget->realization_percentage = 0;
                }

                return $budget;
            });

            // Get unique PICs for filter
            $pics = BudgetCategory::byYear($year)
                ->distinct()
                ->pluck('pic')
                ->filter()
                ->sort()
                ->values();

            return view('budget.realizations', compact(
                'budgets',
                'pics',
                'year'
            ));
        } catch (\Exception $e) {
            Log::error('Error in budget realizations: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memuat data realisasi anggaran.');
        }
    }

    public function realizationDetail($id, Request $request)
    {
        try {
            $budget = BudgetCategory::findOrFail($id);

            // Ensure full_code is available
            if (!$budget->reference) {
                $budget->reference = $budget->kegiatan . $budget->kro_code . $budget->ro_code . $budget->initial_code . $budget->account_code;
                $budget->save();
            }

            // Get bills untuk budget category ini
            $bills = Bill::where('coa', $budget->reference)
                ->with(['creator', 'approver'])
                ->orderBy('tgl_spp', 'desc')
                ->paginate(20);

            return view('budget.realization-detail', compact('budget', 'bills'));
        } catch (\Exception $e) {
            Log::error('Error in budget realization detail: ' . $e->getMessage());
            return redirect()->route('budget.realizations')
                ->with('error', 'Data anggaran tidak ditemukan.');
        }
    }

    public function store(Request $request)
    {
        try {
            // Check permission dengan method yang benar
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')
                    ->with('error', 'Anda tidak memiliki akses untuk menambah data anggaran.');
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
                'year' => 'nullable|integer|min:2020|max:2030',
            ]);

            // Set default year if not provided
            if (!isset($validated['year'])) {
                $validated['year'] = date('Y');
            }

            // Initialize calculated fields
            $validated['sisa_anggaran'] = $validated['budget_allocation'];
            $validated['total_penyerapan'] = 0;
            $validated['tagihan_outstanding'] = 0;
            $validated['is_active'] = true;

            // Initialize monthly realization fields to 0
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

            Log::info('Budget created successfully', [
                'budget_id' => $budget->id,
                'created_by' => Auth::id(),
                'data' => $validated
            ]);

            return redirect()->route('budget.show', $budget->id)
                ->with('success', 'Data anggaran berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Budget store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data anggaran.')
                ->withInput();
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // Check permission
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')
                    ->with('error', 'Anda tidak memiliki akses untuk mengedit data anggaran.');
            }

            $budget = BudgetCategory::findOrFail($id);

            $validated = $request->validate([
                'kegiatan' => 'required|string|max:255',
                'kro_code' => 'required|string|max:255',
                'ro_code' => 'required|string|max:255',
                'initial_code' => 'required|string|max:255',
                'account_code' => 'required|string|max:255',
                'program_kegiatan_output' => 'required|string',
                'pic' => 'required|string|max:255',
                'budget_allocation' => 'required|numeric|min:0',
                'year' => 'required|integer|min:2020|max:2030',
            ]);

            // Store original budget allocation for comparison
            $originalBudgetAllocation = $budget->budget_allocation;

            // Update the budget
            $budget->update($validated);

            // Recalculate sisa_anggaran if budget_allocation changed
            if ($originalBudgetAllocation != $budget->budget_allocation) {
                $budget->sisa_anggaran = $budget->budget_allocation - ($budget->total_penyerapan ?: 0);
                $budget->save();
            }

            // Update realization data to ensure consistency
            $budget->updateRealization();

            Log::info('Budget updated successfully', [
                'budget_id' => $budget->id,
                'updated_by' => Auth::id()
            ]);

            return redirect()->route('budget.show', $budget->id)
                ->with('success', 'Data anggaran berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Budget update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data anggaran.')
                ->withInput();
        }
    }


    public function show($id)
    {
        try {
            $budget = BudgetCategory::findOrFail($id);

            // Ensure we have full_code
            if (!$budget->reference) {
                $budget->reference = $budget->kegiatan . $budget->kro_code . $budget->ro_code . $budget->initial_code . $budget->account_code;
                $budget->save();
            }

            // Get related bills
            $bills = Bill::where('coa', $budget->reference)
                ->with(['creator', 'approver'])
                ->orderBy('tgl_spp', 'desc')
                ->paginate(10);

            $monthlyData = collect([
                ['month' => 1, 'name' => 'Januari', 'total' => $budget->realisasi_jan],
                ['month' => 2, 'name' => 'Februari', 'total' => $budget->realisasi_feb],
                ['month' => 3, 'name' => 'Maret', 'total' => $budget->realisasi_mar],
                ['month' => 4, 'name' => 'April', 'total' => $budget->realisasi_apr],
                ['month' => 5, 'name' => 'Mei', 'total' => $budget->realisasi_mei],
                ['month' => 6, 'name' => 'Juni', 'total' => $budget->realisasi_jun],
                ['month' => 7, 'name' => 'Juli', 'total' => $budget->realisasi_jul],
                ['month' => 8, 'name' => 'Agustus', 'total' => $budget->realisasi_agu],
                ['month' => 9, 'name' => 'September', 'total' => $budget->realisasi_sep],
                ['month' => 10, 'name' => 'Oktober', 'total' => $budget->realisasi_okt],
                ['month' => 11, 'name' => 'November', 'total' => $budget->realisasi_nov],
                ['month' => 12, 'name' => 'Desember', 'total' => $budget->realisasi_des],
            ]);

            return view('budget.show', compact('budget', 'monthlyData', 'bills'));
        } catch (\Exception $e) {
            Log::error('Budget show error: ' . $e->getMessage());
            return redirect()->route('budget.index')
                ->with('error', 'Data anggaran tidak ditemukan.');
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new BudgetExport($request->all()), 'budget-data-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Budget export error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengekspor data anggaran.');
        }
    }

    public function create()
    {
        try {
            // Check permission dengan method yang benar
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')
                    ->with('error', 'Anda tidak memiliki akses untuk menambah data anggaran.');
            }

            return view('budget.create');
        } catch (\Exception $e) {
            Log::error('Budget create error: ' . $e->getMessage());
            return redirect()->route('budget.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman tambah anggaran.');
        }
    }


    public function edit($id)
    {
        try {
            // Check permission
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')
                    ->with('error', 'Anda tidak memiliki akses untuk mengedit data anggaran.');
            }

            $budget = BudgetCategory::findOrFail($id);
            return view('budget.edit', compact('budget'));
        } catch (\Exception $e) {
            Log::error('Budget edit error: ' . $e->getMessage());
            return redirect()->route('budget.index')
                ->with('error', 'Data anggaran tidak ditemukan.');
        }
    }


    public function destroy($id)
    {
        try {
            $budget = BudgetCategory::findOrFail($id);

            // Authorization check
            if (!Auth::user()->canManageBudget()) {
                return redirect()->route('budget.index')
                    ->with('error', 'Anda tidak memiliki akses untuk menghapus data anggaran.');
            }

            // Check if budget has related bills
            $activeBillsCount = Bill::where('coa', $budget->full_code)
                ->whereNotIn('status', ['Dibatalkan'])
                ->count();

            if ($activeBillsCount > 0) {
                return redirect()->route('budget.show', $budget->id)->with(
                    'error',
                    "Tidak dapat menghapus data anggaran. Masih terdapat {$activeBillsCount} tagihan aktif yang terkait dengan anggaran ini."
                );
            }

            // Perform deletion with transaction
            DB::transaction(function () use ($budget) {
                // Delete related cancelled bills if any
                Bill::where('coa', $budget->full_code)
                    ->where('status', 'Dibatalkan')
                    ->delete();

                // Clear related cache
                $budget->clearModelCache();

                // Delete the budget category
                $budget->delete();
            });

            Log::info('Budget category deleted', [
                'id' => $budget->id,
                'code' => $budget->full_code,
                'deleted_by' => Auth::user()->id,
                'deleted_at' => now()
            ]);

            return redirect()->route('budget.index')
                ->with('success', 'Data anggaran berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Budget deletion failed: ' . $e->getMessage());
            return redirect()->route('budget.index')
                ->with('error', 'Gagal menghapus data anggaran. Silakan coba lagi.');
        }
    }
}
