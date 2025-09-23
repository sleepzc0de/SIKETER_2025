<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'no',
        'month',
        'no_spp',
        'nominatif',
        'tgl_spp',
        'jenis_kegiatan',
        'kontraktual_type',
        'nomor_kontrak_spby',
        'no_bast_kuitansi',
        'id_e_perjadin',
        'uraian_spp',
        'bagian',
        'nama_pic',
        'kode_kegiatan',
        'kro',
        'ro',
        'sub_komponen',
        'mak',
        'nomor_surat_tugas_bast_sk',
        'tanggal_st_sk',
        'nomor_undangan',
        'bruto',
        'pajak_ppn',
        'pajak_pph',
        'netto',
        'tanggal_mulai',
        'tanggal_selesai',
        'ls_bendahara',
        'staff_ppk',
        'no_sp2d',
        'tgl_selesai_sp2d',
        'tgl_sp2d',
        'status',
        'coa',
        'posisi_uang',
        'budget_category_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'tgl_spp' => 'date',
        'tanggal_st_sk' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tgl_selesai_sp2d' => 'date',
        'tgl_sp2d' => 'date',
        'bruto' => 'decimal:2',
        'pajak_ppn' => 'decimal:2',
        'pajak_pph' => 'decimal:2',
        'netto' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($bill) {
            // Auto-calculate netto
            $bill->netto = $bill->bruto - $bill->pajak_ppn - $bill->pajak_pph;

            // Auto-generate COA
            if ($bill->kode_kegiatan && $bill->kro && $bill->ro && $bill->sub_komponen && $bill->mak) {
                $bill->coa = $bill->kode_kegiatan . $bill->kro . $bill->ro . $bill->sub_komponen . $bill->mak;
            }
        });

        static::saved(function ($bill) {
            // Update budget realization when status is SP2D
            if ($bill->status === 'Tagihan Telah SP2D' && $bill->budget_category_id) {
                $bill->updateBudgetRealization();
            }
        });

        static::updated(function ($bill) {
            // Handle status change to SP2D
            if ($bill->wasChanged('status') && $bill->status === 'Tagihan Telah SP2D') {
                $bill->updateBudgetRealization();
            }

            // Handle status change from SP2D
            if ($bill->wasChanged('status') && $bill->getOriginal('status') === 'Tagihan Telah SP2D') {
                $bill->updateBudgetRealization();
            }
        });

        static::deleted(function ($bill) {
            // Update budget realization when bill is deleted
            if ($bill->budget_category_id) {
                $bill->updateBudgetRealization();
            }
        });
    }

    public function updateBudgetRealization()
    {
        if (!$this->budget_category_id) return;

        try {
            $budget = $this->budgetCategory;
            if ($budget) {
                $budget->updateRealization();
            }
        } catch (\Exception $e) {
            Log::error('Failed to update budget realization', [
                'bill_id' => $this->id,
                'budget_category_id' => $this->budget_category_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Relationships
    public function budgetCategory()
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors
    public function getTglSppFormattedAttribute()
    {
        return $this->tgl_spp ? $this->tgl_spp->format('d F Y') : null;
    }

    public function getTglSp2dFormattedAttribute()
    {
        return $this->tgl_sp2d ? $this->tgl_sp2d->format('d F Y') : null;
    }

    public function getTglSelesaiSp2dFormattedAttribute()
    {
        return $this->tgl_selesai_sp2d ? $this->tgl_selesai_sp2d->format('d F Y') : null;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Kegiatan Masih Berlangsung' => 'bg-blue-100 text-blue-800',
            'Tagihan Belum Disampaikan oleh Pihak Terkait' => 'bg-yellow-100 text-yellow-800',
            'Tagihan Telah Disampaikan oleh Pihak Terkait' => 'bg-orange-100 text-orange-800',
            'Tagihan Telah Diterbitkan SPP' => 'bg-purple-100 text-purple-800',
            'Tagihan Telah SP2D' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Scopes
    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByBudgetCategory($query, $budgetCategoryId)
    {
        return $query->where('budget_category_id', $budgetCategoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('no_spp', 'ILIKE', "%{$search}%")
              ->orWhere('nominatif', 'ILIKE', "%{$search}%")
              ->orWhere('uraian_spp', 'ILIKE', "%{$search}%")
              ->orWhere('nama_pic', 'ILIKE', "%{$search}%")
              ->orWhere('nomor_kontrak_spby', 'ILIKE', "%{$search}%");
        });
    }

    // Static methods
    public static function getMonthOptions()
    {
        return [
            'Januari' => 'Januari',
            'Februari' => 'Februari',
            'Maret' => 'Maret',
            'April' => 'April',
            'Mei' => 'Mei',
            'Juni' => 'Juni',
            'Juli' => 'Juli',
            'Agustus' => 'Agustus',
            'September' => 'September',
            'Oktober' => 'Oktober',
            'November' => 'November',
            'Desember' => 'Desember',
        ];
    }

    public static function getBagianOptions()
    {
        return [
            'TU' => 'TU',
            'Persija' => 'Persija',
            'MP' => 'MP',
            'Pengelolaan' => 'Pengelolaan',
            'Perencanaan' => 'Perencanaan',
            'Penat' => 'Penat',
        ];
    }

    public static function getKontraktualTypeOptions()
    {
        return [
            'Kontraktual' => 'Kontraktual',
            'Non Kontraktual' => 'Non Kontraktual',
            'GUP' => 'GUP',
            'TUP' => 'TUP',
        ];
    }

    public static function getLsBendaharaOptions()
    {
        return [
            'LS' => 'LS',
            'Bendahara' => 'Bendahara',
        ];
    }

    public static function getStaffPpkOptions()
    {
        return [
            'Diaz' => 'Diaz',
            'Nomo' => 'Nomo',
        ];
    }

    public static function getStatusOptions()
    {
        return [
            'Kegiatan Masih Berlangsung' => 'Kegiatan Masih Berlangsung',
            'Tagihan Belum Disampaikan oleh Pihak Terkait' => 'Tagihan Belum Disampaikan oleh Pihak Terkait',
            'Tagihan Telah Disampaikan oleh Pihak Terkait' => 'Tagihan Telah Disampaikan oleh Pihak Terkait',
            'Tagihan Telah Diterbitkan SPP' => 'Tagihan Telah Diterbitkan SPP',
            'Tagihan Telah SP2D' => 'Tagihan Telah SP2D',
        ];
    }

    public static function getPosisiUangOptions()
    {
        return [
            'Bendahara' => 'Bendahara',
            'Penerima' => 'Penerima',
        ];
    }
}
