<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::with(['budgetCategory', 'creator']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('month')) {
            $query->byMonth($request->month);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }

        if ($request->filled('budget_category_id')) {
            $query->byBudgetCategory($request->budget_category_id);
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_spp', [
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)
            ]);
        }

        $bills = $query->orderBy('tgl_spp', 'desc')->paginate(20);

        // Get filter options
        $months = Bill::getMonthOptions();
        $statuses = Bill::getStatusOptions();
        $bagians = Bill::getBagianOptions();
        $budgetCategories = BudgetCategory::select('id', 'program_kegiatan_output', 'kro_code', 'ro_code', 'account_code')
            ->orderBy('program_kegiatan_output')
            ->get();

        return view('bills.index', compact('bills', 'months', 'statuses', 'bagians', 'budgetCategories'));
    }

    public function create(Request $request)
    {
        // Get dropdown options
        $months = Bill::getMonthOptions();
        $bagians = Bill::getBagianOptions();
        $kontraktualTypes = Bill::getKontraktualTypeOptions();
        $lsBendaharaOptions = Bill::getLsBendaharaOptions();
        $staffPpkOptions = Bill::getStaffPpkOptions();
        $statusOptions = Bill::getStatusOptions();
        $posisiUangOptions = Bill::getPosisiUangOptions();

        // Get distinct values from budget categories
        $kodeKegiatans = BudgetCategory::distinct()->pluck('kegiatan')->filter()->sort()->values();
        $kros = BudgetCategory::distinct()->pluck('kro_code')->filter()->sort()->values();
        $ros = BudgetCategory::distinct()->pluck('ro_code')->filter()->sort()->values();
        $subKomponens = BudgetCategory::distinct()->pluck('initial_code')->filter()->sort()->values();
        $maks = BudgetCategory::distinct()->pluck('account_code')->filter()->sort()->values();

        // Get budget categories for reference
        $budgetCategories = BudgetCategory::select('id', 'program_kegiatan_output', 'kro_code', 'ro_code', 'account_code', 'kegiatan', 'initial_code')
            ->orderBy('program_kegiatan_output')
            ->get();

        // Handle duplicate entries for same tgl_spp
        $duplicateDate = $request->get('duplicate_date');
        $existingBill = null;
        if ($duplicateDate) {
            $existingBill = Bill::where('tgl_spp', $duplicateDate)->first();
        }

        return view('bills.create', compact(
            'months', 'bagians', 'kontraktualTypes', 'lsBendaharaOptions',
            'staffPpkOptions', 'statusOptions', 'posisiUangOptions',
            'kodeKegiatans', 'kros', 'ros', 'subKomponens', 'maks',
            'budgetCategories', 'existingBill', 'duplicateDate'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'no' => 'nullable|string|max:255',
                'month' => 'required|string|in:' . implode(',', array_keys(Bill::getMonthOptions())),
                'no_spp' => 'nullable|string|max:255',
                'nominatif' => 'nullable|string|max:255',
                'tgl_spp' => 'required|date',
                'jenis_kegiatan' => 'nullable|string|max:255',
                'kontraktual_type' => 'nullable|in:' . implode(',', array_keys(Bill::getKontraktualTypeOptions())),
                'nomor_kontrak_spby' => 'nullable|string|max:255',
                'no_bast_kuitansi' => 'nullable|string|max:255',
                'id_e_perjadin' => 'nullable|string|max:255',
                'uraian_spp' => 'nullable|string',
                'bagian' => 'nullable|in:' . implode(',', array_keys(Bill::getBagianOptions())),
                'nama_pic' => 'nullable|string|max:255',
                'kode_kegiatan' => 'nullable|string|max:255',
                'kro' => 'nullable|string|max:255',
                'ro' => 'nullable|string|max:255',
                'sub_komponen' => 'nullable|string|max:255',
                'mak' => 'nullable|string|max:255',
                'nomor_surat_tugas_bast_sk' => 'nullable|string|max:255',
                'tanggal_st_sk' => 'nullable|date',
                'nomor_undangan' => 'nullable|string|max:255',
                'bruto' => 'nullable|numeric|min:0',
                'pajak_ppn' => 'nullable|numeric|min:0',
                'pajak_pph' => 'nullable|numeric|min:0',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'ls_bendahara' => 'nullable|in:' . implode(',', array_keys(Bill::getLsBendaharaOptions())),
                'staff_ppk' => 'nullable|in:' . implode(',', array_keys(Bill::getStaffPpkOptions())),
                'no_sp2d' => 'nullable|string|max:255',
                'tgl_selesai_sp2d' => 'nullable|date',
                'tgl_sp2d' => 'nullable|date',
                'status' => 'required|in:' . implode(',', array_keys(Bill::getStatusOptions())),
                'posisi_uang' => 'nullable|in:' . implode(',', array_keys(Bill::getPosisiUangOptions())),
                'budget_category_id' => 'nullable|exists:budget_categories,id',
            ]);

            // Set default values
            $validated['bruto'] = $validated['bruto'] ?? 0;
            $validated['pajak_ppn'] = $validated['pajak_ppn'] ?? 0;
            $validated['pajak_pph'] = $validated['pajak_pph'] ?? 0;
            $validated['created_by'] = Auth::id();

            // Find matching budget category if not explicitly set
            if (!$validated['budget_category_id'] && $validated['kode_kegiatan'] && $validated['kro'] && $validated['ro'] && $validated['mak']) {
                $budgetCategory = BudgetCategory::where('kegiatan', $validated['kode_kegiatan'])
                    ->where('kro_code', $validated['kro'])
                    ->where('ro_code', $validated['ro'])
                    ->where('account_code', $validated['mak'])
                    ->first();

                if ($budgetCategory) {
                    $validated['budget_category_id'] = $budgetCategory->id;
                }
            }

            $bill = Bill::create($validated);

            Log::info('Bill created successfully', [
                'bill_id' => $bill->id,
                'created_by' => Auth::id(),
                'tgl_spp' => $validated['tgl_spp'],
                'netto' => $bill->netto
            ]);

            return redirect()->route('bills.show', $bill->id)
                ->with('success', 'Tagihan berhasil dibuat.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Bill creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal membuat tagihan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $bill = Bill::with(['budgetCategory', 'creator', 'updater'])->findOrFail($id);

            return view('bills.show', compact('bill'));
        } catch (\Exception $e) {
            Log::error('Bill show error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('bills.index')->with('error', 'Tagihan tidak ditemukan.');
        }
    }

    public function edit($id)
    {
        try {
            $bill = Bill::findOrFail($id);

            // Get dropdown options
            $months = Bill::getMonthOptions();
            $bagians = Bill::getBagianOptions();
            $kontraktualTypes = Bill::getKontraktualTypeOptions();
            $lsBendaharaOptions = Bill::getLsBendaharaOptions();
            $staffPpkOptions = Bill::getStaffPpkOptions();
            $statusOptions = Bill::getStatusOptions();
            $posisiUangOptions = Bill::getPosisiUangOptions();

            // Get distinct values from budget categories
            $kodeKegiatans = BudgetCategory::distinct()->pluck('kegiatan')->filter()->sort()->values();
            $kros = BudgetCategory::distinct()->pluck('kro_code')->filter()->sort()->values();
            $ros = BudgetCategory::distinct()->pluck('ro_code')->filter()->sort()->values();
            $subKomponens = BudgetCategory::distinct()->pluck('initial_code')->filter()->sort()->values();
            $maks = BudgetCategory::distinct()->pluck('account_code')->filter()->sort()->values();

            // Get budget categories for reference
            $budgetCategories = BudgetCategory::select('id', 'program_kegiatan_output', 'kro_code', 'ro_code', 'account_code', 'kegiatan', 'initial_code')
                ->orderBy('program_kegiatan_output')
                ->get();

            return view('bills.edit', compact(
                'bill', 'months', 'bagians', 'kontraktualTypes', 'lsBendaharaOptions',
                'staffPpkOptions', 'statusOptions', 'posisiUangOptions',
                'kodeKegiatans', 'kros', 'ros', 'subKomponens', 'maks', 'budgetCategories'
            ));

        } catch (\Exception $e) {
            Log::error('Bill edit error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('bills.index')->with('error', 'Tagihan tidak ditemukan.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $bill = Bill::findOrFail($id);

            $validated = $request->validate([
                'no' => 'nullable|string|max:255',
                'month' => 'required|string|in:' . implode(',', array_keys(Bill::getMonthOptions())),
                'no_spp' => 'nullable|string|max:255',
                'nominatif' => 'nullable|string|max:255',
                'tgl_spp' => 'required|date',
                'jenis_kegiatan' => 'nullable|string|max:255',
                'kontraktual_type' => 'nullable|in:' . implode(',', array_keys(Bill::getKontraktualTypeOptions())),
                'nomor_kontrak_spby' => 'nullable|string|max:255',
                'no_bast_kuitansi' => 'nullable|string|max:255',
                'id_e_perjadin' => 'nullable|string|max:255',
                'uraian_spp' => 'nullable|string',
                'bagian' => 'nullable|in:' . implode(',', array_keys(Bill::getBagianOptions())),
                'nama_pic' => 'nullable|string|max:255',
                'kode_kegiatan' => 'nullable|string|max:255',
                'kro' => 'nullable|string|max:255',
                'ro' => 'nullable|string|max:255',
                'sub_komponen' => 'nullable|string|max:255',
                'mak' => 'nullable|string|max:255',
                'nomor_surat_tugas_bast_sk' => 'nullable|string|max:255',
                'tanggal_st_sk' => 'nullable|date',
                'nomor_undangan' => 'nullable|string|max:255',
                'bruto' => 'nullable|numeric|min:0',
                'pajak_ppn' => 'nullable|numeric|min:0',
                'pajak_pph' => 'nullable|numeric|min:0',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'ls_bendahara' => 'nullable|in:' . implode(',', array_keys(Bill::getLsBendaharaOptions())),
                'staff_ppk' => 'nullable|in:' . implode(',', array_keys(Bill::getStaffPpkOptions())),
                'no_sp2d' => 'nullable|string|max:255',
                'tgl_selesai_sp2d' => 'nullable|date',
                'tgl_sp2d' => 'nullable|date',
                'status' => 'required|in:' . implode(',', array_keys(Bill::getStatusOptions())),
                'posisi_uang' => 'nullable|in:' . implode(',', array_keys(Bill::getPosisiUangOptions())),
                'budget_category_id' => 'nullable|exists:budget_categories,id',
            ]);

            // Set default values
            $validated['bruto'] = $validated['bruto'] ?? 0;
            $validated['pajak_ppn'] = $validated['pajak_ppn'] ?? 0;
            $validated['pajak_pph'] = $validated['pajak_pph'] ?? 0;
            $validated['updated_by'] = Auth::id();

            // Find matching budget category if not explicitly set
            if (!$validated['budget_category_id'] && $validated['kode_kegiatan'] && $validated['kro'] && $validated['ro'] && $validated['mak']) {
                $budgetCategory = BudgetCategory::where('kegiatan', $validated['kode_kegiatan'])
                    ->where('kro_code', $validated['kro'])
                    ->where('ro_code', $validated['ro'])
                    ->where('account_code', $validated['mak'])
                    ->first();

                if ($budgetCategory) {
                    $validated['budget_category_id'] = $budgetCategory->id;
                }
            }

            $bill->update($validated);

            Log::info('Bill updated successfully', [
                'bill_id' => $bill->id,
                'updated_by' => Auth::id(),
                'changes' => $bill->getChanges()
            ]);

            return redirect()->route('bills.show', $bill->id)
                ->with('success', 'Tagihan berhasil diperbarui.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Bill update failed', [
                'bill_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui tagihan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $bill = Bill::findOrFail($id);

            // Check if bill is SP2D status (might want to prevent deletion)
            if ($bill->status === 'Tagihan Telah SP2D') {
                return redirect()->route('bills.index')
                    ->with('error', 'Tidak dapat menghapus tagihan yang sudah SP2D.');
            }

            $bill->delete();

            Log::info('Bill deleted successfully', [
                'bill_id' => $id,
                'deleted_by' => Auth::id(),
                'bill_data' => $bill->toArray()
            ]);

            return redirect()->route('bills.index')
                ->with('success', 'Tagihan berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Bill deletion failed', [
                'bill_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('bills.index')
                ->with('error', 'Gagal menghapus tagihan.');
        }
    }

    // AJAX endpoints for cascading dropdowns
    public function getKrosByKegiatan(Request $request)
    {
        $kegiatan = $request->get('kegiatan');
        $kros = BudgetCategory::where('kegiatan', $kegiatan)
            ->distinct()
            ->pluck('kro_code')
            ->filter()
            ->sort()
            ->values();

        return response()->json($kros);
    }

    public function getRosByKegiatanKro(Request $request)
    {
        $kegiatan = $request->get('kegiatan');
        $kro = $request->get('kro');

        $ros = BudgetCategory::where('kegiatan', $kegiatan)
            ->where('kro_code', $kro)
            ->distinct()
            ->pluck('ro_code')
            ->filter()
            ->sort()
            ->values();

        return response()->json($ros);
    }

    public function getSubKomponensByKegiatanKroRo(Request $request)
    {
        $kegiatan = $request->get('kegiatan');
        $kro = $request->get('kro');
        $ro = $request->get('ro');

        $subKomponens = BudgetCategory::where('kegiatan', $kegiatan)
            ->where('kro_code', $kro)
            ->where('ro_code', $ro)
            ->distinct()
            ->pluck('initial_code')
            ->filter()
            ->sort()
            ->values();

        return response()->json($subKomponens);
    }

    public function getMaksByAll(Request $request)
    {
        $kegiatan = $request->get('kegiatan');
        $kro = $request->get('kro');
        $ro = $request->get('ro');
        $subKomponen = $request->get('sub_komponen');

        $maks = BudgetCategory::where('kegiatan', $kegiatan)
            ->where('kro_code', $kro)
            ->where('ro_code', $ro)
            ->where('initial_code', $subKomponen)
            ->distinct()
            ->pluck('account_code')
            ->filter()
            ->sort()
            ->values();

        return response()->json($maks);
    }

    public function duplicateForDate(Request $request)
    {
        $date = $request->get('date');
        return redirect()->route('bills.create', ['duplicate_date' => $date]);
    }

    public function exportExcel(Request $request)
    {
        // Implementation for Excel export
        // You can implement this later using Laravel Excel
        return response()->json(['message' => 'Export feature coming soon']);
    }
}
