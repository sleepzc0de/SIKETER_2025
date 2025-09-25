<?php
// app/Models/BudgetCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'kegiatan',
        'kro_code',
        'ro_code',
        'initial_code',
        'account_code',
        'program_kegiatan_output',
        'pic',
        'budget_allocation',
        'reference',
        'reference2',
        'reference_output',
        'length',
        'year',
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
        'realisasi_des',
        'tagihan_outstanding',
        'total_penyerapan',
        'sisa_anggaran',
        'is_active',
    ];

    protected $casts = [
        'budget_allocation' => 'decimal:2',
        'year' => 'integer',
        'realisasi_jan' => 'decimal:2',
        'realisasi_feb' => 'decimal:2',
        'realisasi_mar' => 'decimal:2',
        'realisasi_apr' => 'decimal:2',
        'realisasi_mei' => 'decimal:2',
        'realisasi_jun' => 'decimal:2',
        'realisasi_jul' => 'decimal:2',
        'realisasi_agu' => 'decimal:2',
        'realisasi_sep' => 'decimal:2',
        'realisasi_okt' => 'decimal:2',
        'realisasi_nov' => 'decimal:2',
        'realisasi_des' => 'decimal:2',
        'tagihan_outstanding' => 'decimal:2',
        'total_penyerapan' => 'decimal:2',
        'sisa_anggaran' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($budget) {
            Log::info('BudgetCategory saving', [
                'id' => $budget->id,
                'original' => $budget->getOriginal(),
                'new' => $budget->getAttributes()
            ]);

            // Ensure decimal fields are properly formatted
            $decimalFields = [
                'budget_allocation', 'realisasi_jan', 'realisasi_feb', 'realisasi_mar',
                'realisasi_apr', 'realisasi_mei', 'realisasi_jun', 'realisasi_jul',
                'realisasi_agu', 'realisasi_sep', 'realisasi_okt', 'realisasi_nov',
                'realisasi_des', 'tagihan_outstanding', 'total_penyerapan', 'sisa_anggaran'
            ];

            foreach ($decimalFields as $field) {
                if (isset($budget->{$field})) {
                    $budget->{$field} = round((float) $budget->{$field}, 2);
                }
            }

            // Set year if not provided
            if (!$budget->year) {
                $budget->year = date('Y');
            }

            // Auto-generate reference fields
            if ($budget->kegiatan && $budget->kro_code && $budget->ro_code && $budget->initial_code && $budget->account_code) {
                $budget->reference = $budget->kegiatan . $budget->kro_code . $budget->ro_code . $budget->initial_code . $budget->account_code;
                $budget->reference2 = $budget->kegiatan . $budget->kro_code . $budget->ro_code . $budget->initial_code;
                $budget->reference_output = $budget->kegiatan . $budget->kro_code . $budget->ro_code;
                $budget->length = strlen($budget->reference);
            }
        });

        static::saved(function ($budget) {
            $budget->clearModelCache();
            Log::info('BudgetCategory saved successfully', [
                'id' => $budget->id,
                'saved_data' => $budget->toArray()
            ]);
        });

        static::updated(function ($budget) {
            // Recalculate sisa_anggaran when budget_allocation changes
            if ($budget->wasChanged('budget_allocation')) {
                $budget->sisa_anggaran = $budget->budget_allocation - $budget->total_penyerapan;
                // Use query builder to avoid infinite loop
                DB::table('budget_categories')
                    ->where('id', $budget->id)
                    ->update(['sisa_anggaran' => $budget->sisa_anggaran]);
            }

            Log::info('BudgetCategory updated successfully', [
                'id' => $budget->id,
                'changes' => $budget->getChanges()
            ]);
        });
    }

    // Relationship dengan Bills berdasarkan COA matching
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'coa', 'reference');
    }

    // Alternative relationship method for better performance
    public function getBillsAttribute()
    {
        return Bill::where('coa', $this->reference)->get();
    }

    // Clear model-specific cache
    public function clearModelCache()
    {
        try {
            $cacheKeys = [
                "budget_{$this->id}_total_realization",
                "budget_{$this->id}_realization_percentage",
                "budget_{$this->id}_remaining_budget",
                "budget_{$this->id}_bills_sp2d",
                "budget_{$this->id}_bills_outstanding",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Safe cache flush without tagging
            try {
                Cache::tags(['budget_stats'])->flush();
            } catch (\Exception $e) {
                // Ignore cache tagging errors
                Log::debug('Cache tagging not supported, skipping tag flush');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear model cache', [
                'budget_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Safe cache method with fallback
    private function safeCache($key, $ttl, $callback)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::warning('Cache operation failed, using direct calculation', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $callback();
        }
    }

    // Accessors
    public function getFullCodeAttribute(): string
    {
        return $this->reference ?: ($this->kegiatan . $this->kro_code . $this->ro_code . $this->initial_code . $this->account_code);
    }

    public function getTotalRealizationAttribute()
    {
        return $this->safeCache("budget_{$this->id}_total_realization", 1800, function () {
            return ($this->realisasi_jan ?: 0) + ($this->realisasi_feb ?: 0) + ($this->realisasi_mar ?: 0) +
                   ($this->realisasi_apr ?: 0) + ($this->realisasi_mei ?: 0) + ($this->realisasi_jun ?: 0) +
                   ($this->realisasi_jul ?: 0) + ($this->realisasi_agu ?: 0) + ($this->realisasi_sep ?: 0) +
                   ($this->realisasi_okt ?: 0) + ($this->realisasi_nov ?: 0) + ($this->realisasi_des ?: 0);
        });
    }

    public function getRealizationPercentageAttribute()
    {
        if (($this->budget_allocation ?: 0) <= 0) return 0;

        return $this->safeCache("budget_{$this->id}_realization_percentage", 1800, function () {
            return (($this->total_penyerapan ?: 0) / ($this->budget_allocation ?: 1)) * 100;
        });
    }

    public function getRemainingBudgetAttribute()
    {
        return $this->safeCache("budget_{$this->id}_remaining_budget", 1800, function () {
            return ($this->budget_allocation ?: 0) - ($this->total_penyerapan ?: 0);
        });
    }

    // Get bills yang sudah SP2D
    public function getTotalPenyerapanAttribute()
    {
        if (isset($this->attributes['total_penyerapan'])) {
            return $this->attributes['total_penyerapan'];
        }

        return $this->safeCache("budget_{$this->id}_bills_sp2d", 1800, function () {
            return Bill::where('coa', $this->full_code)
                ->where('status', 'Tagihan Telah SP2D')
                ->sum('amount') ?: 0;
        });
    }

    // Get tagihan outstanding (belum SP2D)
    public function getTagihanOutstandingAttribute()
    {
        if (isset($this->attributes['tagihan_outstanding'])) {
            return $this->attributes['tagihan_outstanding'];
        }

        return $this->safeCache("budget_{$this->id}_bills_outstanding", 1800, function () {
            return Bill::where('coa', $this->full_code)
                ->whereIn('status', [
                    'Kegiatan Masih Berlangsung',
                    'SPP Sedang Diproses',
                    'SPP Sudah Diserahkan ke KPPN'
                ])
                ->sum('amount') ?: 0;
        });
    }

    // Update realization when bills change status
    public function updateRealization()
    {
        try {
            // Get monthly realization from SP2D bills
            $monthlyRealization = Bill::where('coa', $this->full_code)
                ->where('status', 'Tagihan Telah SP2D')
                ->selectRaw('month, SUM(amount) as total_amount')
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            // Get outstanding bills
            $outstanding = Bill::where('coa', $this->full_code)
                ->whereIn('status', [
                    'Kegiatan Masih Berlangsung',
                    'SPP Sedang Diproses',
                    'SPP Sudah Diserahkan ke KPPN'
                ])
                ->sum('amount') ?: 0;

            // Monthly field mapping
            $monthFields = [
                1 => 'realisasi_jan', 2 => 'realisasi_feb', 3 => 'realisasi_mar',
                4 => 'realisasi_apr', 5 => 'realisasi_mei', 6 => 'realisasi_jun',
                7 => 'realisasi_jul', 8 => 'realisasi_agu', 9 => 'realisasi_sep',
                10 => 'realisasi_okt', 11 => 'realisasi_nov', 12 => 'realisasi_des'
            ];

            $updateData = [];
            foreach ($monthFields as $month => $field) {
                $monthRealization = $monthlyRealization->get($month);
                $updateData[$field] = $monthRealization ? $monthRealization->total_amount : 0;
            }

            $totalRealization = array_sum($updateData);
            $updateData['tagihan_outstanding'] = $outstanding;
            $updateData['total_penyerapan'] = $totalRealization;
            $updateData['sisa_anggaran'] = ($this->budget_allocation ?: 0) - $totalRealization;

            // Use query builder to avoid model events loop
            $updated = DB::table('budget_categories')
                ->where('id', $this->id)
                ->update($updateData);

            if ($updated) {
                // Refresh model attributes
                $this->refresh();
                $this->clearModelCache();

                Log::info('Budget realization updated', [
                    'budget_id' => $this->id,
                    'total_realization' => $totalRealization,
                    'outstanding' => $outstanding
                ]);
            }

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to update realization', [
                'budget_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    // Helper method to get monthly field name
    public static function getMonthlyFieldName($month)
    {
        $monthFields = [
            1 => 'realisasi_jan', 2 => 'realisasi_feb', 3 => 'realisasi_mar',
            4 => 'realisasi_apr', 5 => 'realisasi_mei', 6 => 'realisasi_jun',
            7 => 'realisasi_jul', 8 => 'realisasi_agu', 9 => 'realisasi_sep',
            10 => 'realisasi_okt', 11 => 'realisasi_nov', 12 => 'realisasi_des'
        ];
        return $monthFields[$month] ?? null;
    }

    // Force refresh model from database
    public function forceRefresh()
    {
        $fresh = $this->newQuery()->find($this->id);
        if ($fresh) {
            $this->setRawAttributes($fresh->getAttributes());
            $this->syncOriginal();
            $this->clearModelCache();
        }
        return $this;
    }

    // Relationships
    public function budgetRealizations()
    {
        return $this->hasMany(BudgetRealization::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByYear($query, $year = null)
    {
        return $query->where('year', $year ?: date('Y'));
    }

    public function scopeByPIC($query, $pic)
    {
        return $query->where('pic', 'like', "%{$pic}%");
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('program_kegiatan_output', 'ILIKE', "%{$search}%")
              ->orWhere('kro_code', 'ILIKE', "%{$search}%")
              ->orWhere('ro_code', 'ILIKE', "%{$search}%")
              ->orWhere('account_code', 'ILIKE', "%{$search}%")
              ->orWhere('pic', 'ILIKE', "%{$search}%");
        });
    }
}
