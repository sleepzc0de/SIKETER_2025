<?php
// app/Models/Bill.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Basic Information
        'no',
        'month',
        'year',
        'no_spp',
        'nominatif',
        'tgl_spp',
        'jenis_kegiatan',

        // Contract/Document Information
        'kontraktual_type',
        'nomor_kontrak_spby',
        'no_bast_kuitansi',
        'id_e_perjadin',
        'nomor_surat_tugas_bast_sk',
        'tanggal_st_sk',
        'nomor_undangan',
        'uraian_spp',

        // Organization & Coding
        'bagian',
        'nama_pic',
        'kode_kegiatan',
        'kro',
        'ro',
        'sub_komponen',
        'mak',
        'coa',

        // Financial Information
        'bruto',
        'pajak_ppn',
        'pajak_pph',
        'netto',
        'amount',
        'tanggal_mulai',
        'tanggal_selesai',

        // Staff & Payment Information
        'ls_bendahara',
        'staff_ppk',
        'no_sp2d',
        'tgl_selesai_sp2d',
        'tgl_sp2d',

        // Status & Position
        'status',
        'posisi_uang',

        // Approval Information
        'approved_at',
        'approved_by',
        'approval_notes',

        // System Information
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tgl_spp' => 'date',
        'tanggal_st_sk' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tgl_selesai_sp2d' => 'date',
        'tgl_sp2d' => 'date',
        'approved_at' => 'datetime',
        'bruto' => 'decimal:2',
        'pajak_ppn' => 'decimal:2',
        'pajak_pph' => 'decimal:2',
        'netto' => 'decimal:2',
        'amount' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
    ];

    // Define constants for enum values
    const KONTRAKTUAL_TYPES = [
        'Kontraktual' => 'Kontraktual',
        'Non Kontraktual' => 'Non Kontraktual',
        'GUP' => 'GUP',
        'TUP' => 'TUP',
    ];

    const BAGIAN_OPTIONS = [
        'Kepala Kantor' => 'Kepala Kantor',
        'Kasubag TU' => 'Kasubag TU',
        'Bendahara' => 'Bendahara',
        'Operator SAKTI' => 'Operator SAKTI',
        'Bagian Umum' => 'Bagian Umum',
        'Bagian Kepegawaian' => 'Bagian Kepegawaian',
        'Bagian Keuangan' => 'Bagian Keuangan',
        'Seksi Pengawasan I' => 'Seksi Pengawasan I',
        'Seksi Pengawasan II' => 'Seksi Pengawasan II',
        'Seksi Pengawasan III' => 'Seksi Pengawasan III',
        'Seksi Investigasi' => 'Seksi Investigasi',
        'Seksi Pengembangan Pengawasan' => 'Seksi Pengembangan Pengawasan',
        'APIP' => 'APIP',
        'Pejabat Penandatangan SPM' => 'Pejabat Penandatangan SPM',
    ];

    const STATUS_OPTIONS = [
        'Kegiatan Masih Berlangsung' => 'Kegiatan Masih Berlangsung',
        'SPP Sedang Diproses' => 'SPP Sedang Diproses',
        'SPP Sudah Diserahkan ke KPPN' => 'SPP Sudah Diserahkan ke KPPN',
        'Tagihan Telah SP2D' => 'Tagihan Telah SP2D',
        'Dibatalkan' => 'Dibatalkan',
    ];

    const LS_BENDAHARA_OPTIONS = [
        'LS' => 'LS (Langsung)',
        'Bendahara' => 'Bendahara',
    ];

    const STAFF_PPK_OPTIONS = [
        'Kepala Kantor' => 'Kepala Kantor',
        'Kasubag TU' => 'Kasubag TU',
        'Bendahara' => 'Bendahara',
        'Pejabat Penandatangan SPM' => 'Pejabat Penandatangan SPM',
        'Staff Keuangan' => 'Staff Keuangan',
    ];

    const POSISI_UANG_OPTIONS = [
        'Kas Negara' => 'Kas Negara',
        'Kas Daerah' => 'Kas Daerah',
        'Bendahara Pengeluaran' => 'Bendahara Pengeluaran',
        'Rekening Pihak Ketiga' => 'Rekening Pihak Ketiga',
    ];

    const MONTHS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getMonthNameAttribute(): string
    {
        return self::MONTHS[$this->month] ?? '';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedBrutoAttribute(): string
    {
        return 'Rp ' . number_format($this->bruto, 0, ',', '.');
    }

    public function getFormattedNettoAttribute(): string
    {
        return 'Rp ' . number_format($this->netto, 0, ',', '.');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Kegiatan Masih Berlangsung' => 'bg-yellow-100 text-yellow-800',
            'SPP Sedang Diproses' => 'bg-blue-100 text-blue-800',
            'SPP Sudah Diserahkan ke KPPN' => 'bg-indigo-100 text-indigo-800',
            'Tagihan Telah SP2D' => 'bg-green-100 text-green-800',
            'Dibatalkan' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Mutators
    public function setNettoAttribute($value)
    {
        $this->attributes['netto'] = $value;
        $this->attributes['amount'] = $value; // Keep amount in sync with netto
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value;
        if (!isset($this->attributes['netto']) || $this->attributes['netto'] == 0) {
            $this->attributes['netto'] = $value;
        }
    }

    // Boot method for auto-calculating fields
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($bill) {
            // Auto-calculate netto if not set
            if ($bill->bruto || $bill->pajak_ppn || $bill->pajak_pph) {
                $calculated_netto = ($bill->bruto ?: 0) - ($bill->pajak_ppn ?: 0) - ($bill->pajak_pph ?: 0);
                $bill->netto = max(0, $calculated_netto);
                $bill->amount = $bill->netto;
            }

            // Auto-generate COA if components are available
            if ($bill->kode_kegiatan || $bill->kro || $bill->ro || $bill->sub_komponen || $bill->mak) {
                $bill->coa = ($bill->kode_kegiatan ?: '') .
                           ($bill->kro ?: '') .
                           ($bill->ro ?: '') .
                           ($bill->sub_komponen ?: '') .
                           ($bill->mak ?: '');
            }

            // Set year if not set
            if (!$bill->year) {
                $bill->year = $bill->tgl_spp ? Carbon::parse($bill->tgl_spp)->year : date('Y');
            }
        });
    }

    // Scopes
    public function scopeByYear($query, $year = null)
    {
        return $query->where('year', $year ?: date('Y'));
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByBagian($query, $bagian)
    {
        return $query->where('bagian', $bagian);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    // Helper methods
    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function canBeDeleted(): bool
    {
        return !$this->isApproved() && $this->status !== 'Tagihan Telah SP2D';
    }

    public function canBeEdited(): bool
    {
        return $this->status !== 'Tagihan Telah SP2D' || auth()->user()?->hasRole('super-admin');
    }

    public static function getMonthOptions()
{
    return self::MONTHS;
}

public static function getStatusOptions()
{
    return self::STATUS_OPTIONS;
}

public static function getBagianOptions()
{
    return self::BAGIAN_OPTIONS;
}

public static function getKontraktualTypeOptions()
{
    return self::KONTRAKTUAL_TYPES;
}

public static function getLsBendaharaOptions()
{
    return self::LS_BENDAHARA_OPTIONS;
}

public static function getStaffPpkOptions()
{
    return self::STAFF_PPK_OPTIONS;
}

public static function getPosisiUangOptions()
{
    return self::POSISI_UANG_OPTIONS;
}

public function getTglSppFormattedAttribute(): string
{
    return $this->tgl_spp ? $this->tgl_spp->format('d/m/Y') : '-';
}

public function scopeSearch($query, $search)
{
    return $query->where(function($q) use ($search) {
        $q->where('no_spp', 'like', "%{$search}%")
          ->orWhere('nominatif', 'like', "%{$search}%")
          ->orWhere('nama_pic', 'like', "%{$search}%")
          ->orWhere('uraian_spp', 'like', "%{$search}%");
    });
}

public function scopeByBudgetCategory($query, $budgetCategoryId)
{
    // Since we're using COA matching, find the budget category and match COA
    $budgetCategory = BudgetCategory::find($budgetCategoryId);
    if ($budgetCategory) {
        return $query->where('coa', $budgetCategory->full_code);
    }
    return $query;
}

public function budgetCategory()
{
    return $this->belongsTo(BudgetCategory::class, 'coa', 'reference');
}



}
