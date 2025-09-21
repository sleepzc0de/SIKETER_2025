<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::with(['budgetCategory', 'creator', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        } else {
            $query->where('year', date('Y'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhereHas('budgetCategory', function ($bq) use ($search) {
                      $bq->where('program_kegiatan_output', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $bills = $query->orderBy('created_at', 'desc')->paginate(20);

        $budgetCategories = Cache::remember('budget_categories_for_bills', 3600, function () {
            return BudgetCategory::active()->select('id', 'kro_code', 'ro_code', 'account_code', 'program_kegiatan_output')->get();
        });

        return view('bills.index', compact('bills', 'budgetCategories'));
    }

    public function create()
    {
        $budgetCategories = Cache::remember('budget_categories_for_bills', 3600, function () {
            return BudgetCategory::active()->get();
        });

        return view('bills.create', compact('budgetCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_category_id' => 'required|exists:budget_categories,id',
            'bill_number' => 'required|string|unique:bills,bill_number',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030',
            'bill_date' => 'required|date',
            'description' => 'required|string',
        ]);

        $validated['created_by'] = Auth::id();

        DB::transaction(function () use ($validated) {
            Bill::create($validated);
        });

        return redirect()->route('bills.index')->with('success', 'Tagihan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $bill = Bill::with(['budgetCategory', 'creator', 'approver'])->findOrFail($id);
        return view('bills.show', compact('bill'));
    }

    public function edit($id)
    {
        $bill = Bill::findOrFail($id);

        if ($bill->status === 'sp2d') {
            return redirect()->route('bills.show', $bill->id)->with('error', 'Tagihan yang sudah SP2D tidak dapat diubah.');
        }

        $budgetCategories = Cache::remember('budget_categories_for_bills', 3600, function () {
            return BudgetCategory::active()->get();
        });

        return view('bills.edit', compact('bill', 'budgetCategories'));
    }

    public function update(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);

        if ($bill->status === 'sp2d') {
            return redirect()->route('bills.show', $bill->id)->with('error', 'Tagihan yang sudah SP2D tidak dapat diubah.');
        }

        $validated = $request->validate([
            'budget_category_id' => 'required|exists:budget_categories,id',
            'bill_number' => 'required|string|unique:bills,bill_number,' . $bill->id,
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2030',
            'bill_date' => 'required|date',
            'description' => 'required|string',
        ]);

        DB::transaction(function () use ($bill, $validated) {
            $bill->update($validated);
        });

        return redirect()->route('bills.show', $bill->id)->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,sp2d,cancelled',
            'sp2d_number' => 'required_if:status,sp2d|nullable|string',
            'sp2d_date' => 'required_if:status,sp2d|nullable|date',
        ]);

        if (!Auth::user()->canApprove()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah status tagihan.');
        }

        DB::transaction(function () use ($bill, $validated) {
            $updateData = ['status' => $validated['status']];

            if ($validated['status'] === 'sp2d') {
                $updateData['sp2d_number'] = $validated['sp2d_number'];
                $updateData['sp2d_date'] = $validated['sp2d_date'];
                $updateData['approved_by'] = Auth::id();
                $updateData['approved_at'] = now();
            }

            $bill->update($updateData);
        });

        $statusText = [
            'pending' => 'Pending',
            'sp2d' => 'SP2D',
            'cancelled' => 'Dibatalkan'
        ];

        return redirect()->route('bills.show', $bill->id)->with('success', "Status tagihan berhasil diubah menjadi {$statusText[$validated['status']]}.");
    }

    public function destroy($id)
    {
        $bill = Bill::findOrFail($id);

        if ($bill->status === 'sp2d') {
            return redirect()->route('bills.index')->with('error', 'Tagihan yang sudah SP2D tidak dapat dihapus.');
        }

        if (!Auth::user()->canApprove() && $bill->created_by !== Auth::id()) {
            return redirect()->route('bills.index')->with('error', 'Anda tidak memiliki akses untuk menghapus tagihan ini.');
        }

        DB::transaction(function () use ($bill) {
            $bill->delete();
        });

        return redirect()->route('bills.index')->with('success', 'Tagihan berhasil dihapus.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'bill_ids' => 'required|array',
            'bill_ids.*' => 'exists:bills,id',
            'status' => 'required|in:sp2d,cancelled',
            'sp2d_number' => 'required_if:status,sp2d|nullable|string',
            'sp2d_date' => 'required_if:status,sp2d|nullable|date',
        ]);

        if (!Auth::user()->canApprove()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah status tagihan.');
        }

        DB::transaction(function () use ($validated) {
            $updateData = ['status' => $validated['status']];

            if ($validated['status'] === 'sp2d') {
                $updateData['sp2d_number'] = $validated['sp2d_number'];
                $updateData['sp2d_date'] = $validated['sp2d_date'];
                $updateData['approved_by'] = Auth::id();
                $updateData['approved_at'] = now();
            }

            Bill::whereIn('id', $validated['bill_ids'])
                ->where('status', 'pending')
                ->update($updateData);
        });

        return redirect()->route('bills.index')->with('success', 'Status tagihan berhasil diperbarui secara massal.');
    }
}
