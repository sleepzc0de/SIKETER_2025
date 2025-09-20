<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\BudgetRealization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = BudgetCategory::with('budgetRealizations');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                  ->orWhere('kro_code', 'ILIKE', "%{$search}%")
                  ->orWhere('account_code', 'ILIKE', "%{$search}%")
                  ->orWhere('pic', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('pic')) {
            $query->where('pic', $request->pic);
        }

        $budgets = $query->paginate(15);

        $pics = BudgetCategory::distinct()->pluck('pic');

        return view('budget.index', compact('budgets', 'pics'));
    }

    public function show(BudgetCategory $budget)
    {
        $budget->load(['budgetRealizations.creator']);

        $monthlyData = $budget->budgetRealizations()
            ->select(
                DB::raw('EXTRACT(month FROM created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy(DB::raw('EXTRACT(month FROM created_at)'))
            ->orderBy('month')
            ->get();

        return view('budget.show', compact('budget', 'monthlyData'));
    }

    public function create()
    {
        return view('budget.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kro_code' => 'required|string|max:255',
            'ro_code' => 'required|string|max:255',
            'initial_code' => 'required|string|max:255',
            'account_code' => 'required|string|max:255',
            'description' => 'required|string',
            'pic' => 'required|string|max:255',
            'budget_allocation' => 'required|numeric|min:0',
            'reference' => 'required|string|max:255',
            'reference2' => 'nullable|string|max:255',
            'reference_output' => 'nullable|string|max:255',
            'length' => 'required|integer|min:1',
        ]);

        BudgetCategory::create($validated);

        return redirect()->route('budget.index')->with('success', 'Data anggaran berhasil ditambahkan.');
    }

    public function edit(BudgetCategory $budget)
    {
        return view('budget.edit', compact('budget'));
    }

    public function update(Request $request, BudgetCategory $budget)
    {
        $validated = $request->validate([
            'kro_code' => 'required|string|max:255',
            'ro_code' => 'required|string|max:255',
            'initial_code' => 'required|string|max:255',
            'account_code' => 'required|string|max:255',
            'description' => 'required|string',
            'pic' => 'required|string|max:255',
            'budget_allocation' => 'required|numeric|min:0',
            'reference' => 'required|string|max:255',
            'reference2' => 'nullable|string|max:255',
            'reference_output' => 'nullable|string|max:255',
            'length' => 'required|integer|min:1',
        ]);

        $budget->update($validated);

        return redirect()->route('budget.show', $budget)->with('success', 'Data anggaran berhasil diperbarui.');
    }

    public function destroy(BudgetCategory $budget)
    {
        try {
            $budget->delete();
            return redirect()->route('budget.index')->with('success', 'Data anggaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('budget.index')->with('error', 'Gagal menghapus data anggaran. Data mungkin masih digunakan.');
        }
    }
}
