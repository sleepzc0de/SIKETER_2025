<?php
// app/Http/Controllers/BillController.php

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
        try {
            $query = Bill::with(['creator']);

            // Apply filters
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('month')) {
                $query->byMonth($request->month);
            }

            if ($request->filled('year')) {
                $query->byYear($request->year);
            }

            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            if ($request->filled('bagian')) {
                $query->where('bagian', $request->bagian);
            }

            // Date range filter
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tgl_spp', [
                    Carbon::parse($request->start_date),
                    Carbon::parse($request->end_date)
                ]);
            }

            $bills = $query->orderBy('tgl_spp', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20)
                ->withQueryString();

            // Get filter options
            $months = Bill::getMonthOptions();
            $statuses = Bill::getStatusOptions();
            $bagians = Bill::getBagianOptions();

            return view('bills.index', compact('bills', 'months', 'statuses', 'bagians'));

        } catch (\Exception $e) {
            Log::error('Bills index error: ' . $e->getMessage());
            return view('bills.index', [
                'bills' => collect(),
                'months' => Bill::getMonthOptions(),
                'statuses' => Bill::getStatusOptions(),
                'bagians' => Bill::getBagianOptions(),
            ])->with('error', 'Terjadi kesalahan saat memuat data tagihan.');
        }
    }

    public function create(Request $request)
{
    try {
        $duplicateDate = $request->get('duplicate_date');
        $existingBill = null;

        if ($duplicateDate) {
            $existingBill = Bill::where('tgl_spp', $duplicateDate)->first();
        }

        // Get all required data for form
        $months = Bill::MONTHS;
        $kontraktualTypes = Bill::KONTRAKTUAL_TYPES;
        $bagians = Bill::BAGIAN_OPTIONS;
        $statusOptions = Bill::STATUS_OPTIONS;
        $lsBendaharaOptions = Bill::LS_BENDAHARA_OPTIONS;
        $staffPpkOptions = Bill::STAFF_PPK_OPTIONS;
        $posisiUangOptions = Bill::POSISI_UANG_OPTIONS;

        // Get coding options from budget categories
        $kodeKegiatans = BudgetCategory::distinct()
            ->pluck('kegiatan')
            ->filter()
            ->sort()
            ->values();

        return view('bills.create', compact(
            'duplicateDate',
            'existingBill',
            'months',
            'kontraktualTypes',
            'bagians',
            'statusOptions',
            'lsBendaharaOptions',
            'staffPpkOptions',
            'posisiUangOptions',
            'kodeKegiatans'
        ));

    } catch (\Exception $e) {
        Log::error('Error in bills create: ' . $e->getMessage());
        return redirect()->route('bills.index')
            ->with('error', 'Terjadi kesalahan saat memuat halaman tambah tagihan.');
    }
}

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'no' => 'nullable|string|max:255',
                'month' => 'required|integer|between:1,12',
                'no_spp' => 'nullable|string|max:255',
                'nominatif' => 'nullable|string|max:255',
                'tgl_spp' => 'required|date',
                'jenis_kegiatan' => 'nullable|string|max:255',
                'kontraktual_type' => 'nullable|in:' . implode(',', array_keys(Bill::KONTRAKTUAL_TYPES)),
                'nomor_kontrak_spby' => 'nullable|string|max:255',
                'no_bast_kuitansi' => 'nullable|string|max:255',
                'id_e_perjadin' => 'nullable|string|max:255',
                'uraian_spp' => 'nullable|string',
                'bagian' => 'nullable|in:' . implode(',', array_keys(Bill::BAGIAN_OPTIONS)),
                'nama_pic' => 'nullable|string|max:255',
                'kode_kegiatan' => 'nullable|string|max:255',
                'kro' => 'nullable|string|max:255',
                'ro' => 'nullable|string|max:255',
                'sub_komponen' => 'nullable|string|max:255',
                'mak' => 'nullable|string|max:255',
                'coa' => 'nullable|string|max:255',
                'nomor_surat_tugas_bast_sk' => 'nullable|string|max:255',
                'tanggal_st_sk' => 'nullable|date',
                'nomor_undangan' => 'nullable|string|max:255',
                'bruto' => 'nullable|numeric|min:0',
                'pajak_ppn' => 'nullable|numeric|min:0',
                'pajak_pph' => 'nullable|numeric|min:0',
                'netto' => 'nullable|numeric|min:0',
                'amount' => 'nullable|numeric|min:0',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'ls_bendahara' => 'nullable|in:' . implode(',', array_keys(Bill::LS_BENDAHARA_OPTIONS)),
                'staff_ppk' => 'nullable|in:' . implode(',', array_keys(Bill::STAFF_PPK_OPTIONS)),
                'no_sp2d' => 'nullable|string|max:255',
                'tgl_selesai_sp2d' => 'nullable|date',
                'tgl_sp2d' => 'nullable|date',
                'status' => 'required|in:' . implode(',', array_keys(Bill::STATUS_OPTIONS)),
                'posisi_uang' => 'nullable|in:' . implode(',', array_keys(Bill::POSISI_UANG_OPTIONS)),
            ]);

            // Set default values
            $validated['bruto'] = $validated['bruto'] ?? 0;
            $validated['pajak_ppn'] = $validated['pajak_ppn'] ?? 0;
            $validated['pajak_pph'] = $validated['pajak_pph'] ?? 0;
            $validated['created_by'] = Auth::id();

            $bill = Bill::create($validated);

            // Update budget category realization
            if ($bill->coa) {
                $budgetCategory = BudgetCategory::where('reference', $bill->coa)->first();
                if ($budgetCategory) {
                    $budgetCategory->updateRealization();
                }
            }

            Log::info('Bill created successfully', [
                'bill_id' => $bill->id,
                'created_by' => Auth::id(),
                'tgl_spp' => $validated['tgl_spp'],
                'amount' => $bill->amount,
                'year' => $bill->year
            ]);

            return redirect()->route('bills.show', $bill->id)
                ->with('success', 'Tagihan berhasil dibuat.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Bill creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal membuat tagihan.')
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $bill = Bill::with(['creator', 'updater', 'approver'])->findOrFail($id);
            return view('bills.show', compact('bill'));
        } catch (\Exception $e) {
            Log::error('Bill show error: ' . $e->getMessage());
            return redirect()->route('bills.index')
                ->with('error', 'Tagihan tidak ditemukan.');
        }
    }

    public function edit(Bill $bill)
    {
        try {
            // Get all required data for form
            $months = Bill::MONTHS;
            $kontraktualTypes = Bill::KONTRAKTUAL_TYPES;
            $bagians = Bill::BAGIAN_OPTIONS;
            $statusOptions = Bill::STATUS_OPTIONS;
            $lsBendaharaOptions = Bill::LS_BENDAHARA_OPTIONS;
            $staffPpkOptions = Bill::STAFF_PPK_OPTIONS;
            $posisiUangOptions = Bill::POSISI_UANG_OPTIONS;

            // Get coding options from budget categories
            $kodeKegiatans = BudgetCategory::distinct()
                ->pluck('kegiatan')
                ->filter()
                ->sort()
                ->values();

            return view('bills.edit', compact(
                'bill',
                'months',
                'kontraktualTypes',
                'bagians',
                'statusOptions',
                'lsBendaharaOptions',
                'staffPpkOptions',
                'posisiUangOptions',
                'kodeKegiatans'
            ));

        } catch (\Exception $e) {
            Log::error('Error in bills edit: ' . $e->getMessage());
            return redirect()->route('bills.index')
                ->with('error', 'Terjadi kesalahan saat memuat halaman edit tagihan.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $bill = Bill::findOrFail($id);
            $oldCoa = $bill->coa;

            $validated = $request->validate([
                'no' => 'nullable|string|max:255',
                'month' => 'required|integer|between:1,12',
                'no_spp' => 'nullable|string|max:255',
                'nominatif' => 'nullable|string|max:255',
                'tgl_spp' => 'required|date',
                'jenis_kegiatan' => 'nullable|string|max:255',
                'kontraktual_type' => 'nullable|in:' . implode(',', array_keys(Bill::KONTRAKTUAL_TYPES)),
                'nomor_kontrak_spby' => 'nullable|string|max:255',
                'no_bast_kuitansi' => 'nullable|string|max:255',
                'id_e_perjadin' => 'nullable|string|max:255',
                'uraian_spp' => 'nullable|string',
                'bagian' => 'nullable|in:' . implode(',', array_keys(Bill::BAGIAN_OPTIONS)),
                'nama_pic' => 'nullable|string|max:255',
                'kode_kegiatan' => 'nullable|string|max:255',
                'kro' => 'nullable|string|max:255',
                'ro' => 'nullable|string|max:255',
                'sub_komponen' => 'nullable|string|max:255',
                'mak' => 'nullable|string|max:255',
                'coa' => 'nullable|string|max:255',
                'nomor_surat_tugas_bast_sk' => 'nullable|string|max:255',
                'tanggal_st_sk' => 'nullable|date',
                'nomor_undangan' => 'nullable|string|max:255',
                'bruto' => 'nullable|numeric|min:0',
                'pajak_ppn' => 'nullable|numeric|min:0',
                'pajak_pph' => 'nullable|numeric|min:0',
                'netto' => 'nullable|numeric|min:0',
                'amount' => 'nullable|numeric|min:0',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'ls_bendahara' => 'nullable|in:' . implode(',', array_keys(Bill::LS_BENDAHARA_OPTIONS)),
                'staff_ppk' => 'nullable|in:' . implode(',', array_keys(Bill::STAFF_PPK_OPTIONS)),
                'no_sp2d' => 'nullable|string|max:255',
                'tgl_selesai_sp2d' => 'nullable|date',
                'tgl_sp2d' => 'nullable|date',
                'status' => 'required|in:' . implode(',', array_keys(Bill::STATUS_OPTIONS)),
                'posisi_uang' => 'nullable|in:' . implode(',', array_keys(Bill::POSISI_UANG_OPTIONS)),
            ]);

            // Set default values
            $validated['bruto'] = $validated['bruto'] ?? 0;
            $validated['pajak_ppn'] = $validated['pajak_ppn'] ?? 0;
            $validated['pajak_pph'] = $validated['pajak_pph'] ?? 0;
            $validated['updated_by'] = Auth::id();

            $bill->update($validated);

            // Update budget category realization if COA changed
            if ($oldCoa && $oldCoa !== $bill->coa) {
                // Update old budget category
                $oldBudgetCategory = BudgetCategory::where('reference', $oldCoa)->first();
                if ($oldBudgetCategory) {
                    $oldBudgetCategory->updateRealization();
                }
            }

            // Update new budget category
            if ($bill->coa) {
                $budgetCategory = BudgetCategory::where('reference', $bill->coa)->first();
                if ($budgetCategory) {
                    $budgetCategory->updateRealization();
                }
            }

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
            Log::error('Bill update failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui tagihan.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $bill = Bill::findOrFail($id);
            $coa = $bill->coa;

            // Check if bill is SP2D status (might want to prevent deletion)
            if ($bill->status === 'Tagihan Telah SP2D') {
                return redirect()->route('bills.index')
                    ->with('error', 'Tidak dapat menghapus tagihan yang sudah SP2D.');
            }

            $bill->delete();

            // Update budget category realization
            if ($coa) {
                $budgetCategory = BudgetCategory::where('reference', $coa)->first();
                if ($budgetCategory) {
                    $budgetCategory->updateRealization();
                }
            }

            Log::info('Bill deleted successfully', [
                'bill_id' => $id,
                'deleted_by' => Auth::id()
            ]);

            return redirect()->route('bills.index')
                ->with('success', 'Tagihan berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Bill deletion failed: ' . $e->getMessage());
            return redirect()->route('bills.index')
                ->with('error', 'Gagal menghapus tagihan.');
        }
    }

    // AJAX endpoints for cascading dropdowns
    public function getKrosByKegiatan(Request $request)
    {
        try {
            $kegiatan = $request->get('kegiatan');
            if (!$kegiatan) {
                return response()->json([]);
            }

            $kros = BudgetCategory::where('kegiatan', $kegiatan)
                ->distinct()
                ->pluck('kro_code')
                ->filter()
                ->sort()
                ->values();

            return response()->json($kros);
        } catch (\Exception $e) {
            Log::error('Error getting KROs by kegiatan', [
                'kegiatan' => $kegiatan ?? null,
                'error' => $e->getMessage()
            ]);
            return response()->json([], 500);
        }
    }

    public function getRosByKegiatanKro(Request $request)
    {
        try {
            $kegiatan = $request->get('kegiatan');
            $kro = $request->get('kro');

            if (!$kegiatan || !$kro) {
                return response()->json([]);
            }

            $ros = BudgetCategory::where('kegiatan', $kegiatan)
                ->where('kro_code', $kro)
                ->distinct()
                ->pluck('ro_code')
                ->filter()
                ->sort()
                ->values();

            return response()->json($ros);
        } catch (\Exception $e) {
            Log::error('Error getting ROs by kegiatan and kro', [
                'kegiatan' => $kegiatan ?? null,
                'kro' => $kro ?? null,
                'error' => $e->getMessage()
            ]);
            return response()->json([], 500);
        }
    }

    public function getSubKomponensByKegiatanKroRo(Request $request)
    {
        try {
            $kegiatan = $request->get('kegiatan');
            $kro = $request->get('kro');
            $ro = $request->get('ro');

            if (!$kegiatan || !$kro || !$ro) {
                return response()->json([]);
            }

            $subKomponens = BudgetCategory::where('kegiatan', $kegiatan)
                ->where('kro_code', $kro)
                ->where('ro_code', $ro)
                ->distinct()
                ->pluck('initial_code')
                ->filter()
                ->sort()
                ->values();

            return response()->json($subKomponens);
        } catch (\Exception $e) {
            Log::error('Error getting sub komponens', [
                'kegiatan' => $kegiatan ?? null,
                'kro' => $kro ?? null,
                'ro' => $ro ?? null,
                'error' => $e->getMessage()
            ]);
            return response()->json([], 500);
        }
    }

    public function getMaksByAll(Request $request)
    {
        try {
            $kegiatan = $request->get('kegiatan');
            $kro = $request->get('kro');
            $ro = $request->get('ro');
            $subKomponen = $request->get('sub_komponen');

            if (!$kegiatan || !$kro || !$ro || !$subKomponen) {
                return response()->json([]);
            }

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
        } catch (\Exception $e) {
            Log::error('Error getting MAKs', [
                'kegiatan' => $kegiatan ?? null,
                'kro' => $kro ?? null,
                'ro' => $ro ?? null,
                'sub_komponen' => $subKomponen ?? null,
                'error' => $e->getMessage()
            ]);
            return response()->json([], 500);
        }
    }

    public function duplicateForDate(Request $request)
    {
        $date = $request->get('date');
        return redirect()->route('bills.create', ['duplicate_date' => $date]);
    }

    public function exportExcel(Request $request)
    {
        // Implementation for Excel export
        return response()->json(['message' => 'Export feature coming soon']);
    }
}
