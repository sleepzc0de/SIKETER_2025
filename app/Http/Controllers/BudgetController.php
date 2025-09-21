<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Bill;
use App\Models\BudgetRealization;
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

        return redirect()->route('budget.index')->with('success', 'Data anggaran berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $budget = BudgetCategory::findOrFail($id);
        return view('budget.edit', compact('budget'));
    }

    public function update(Request $request, $id)
    {
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
            'reference' => 'required|string|max:255',
            'reference2' => 'nullable|string|max:255',
            'reference_output' => 'nullable|string|max:255',
            'length' => 'required|integer|min:1',
        ]);

        $budget->update($validated);
        $budget->updateRealization();

        return redirect()->route('budget.show', $budget->id)->with('success', 'Data anggaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $budget = BudgetCategory::findOrFail($id);

            DB::transaction(function () use ($budget) {
                // Delete related bills first
                $budget->bills()->delete();
                $budget->delete();
            });

            return redirect()->route('budget.index')->with('success', 'Data anggaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('budget.index')->with('error', 'Gagal menghapus data anggaran.');
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new BudgetExport($request->all()), 'budget-data-' . date('Y-m-d') . '.xlsx');
    }

    // Budget Realizations Methods
    public function realizations(Request $request)
    {
        $query = BudgetCategory::with(['bills' => function($q) {
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
        $budget = BudgetCategory::with(['bills' => function($q) {
            $q->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('budget.realization-detail', compact('budget'));
    }
}
