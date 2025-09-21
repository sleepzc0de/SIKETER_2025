<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_category_id',
        'bill_number',
        'amount',
        'month',
        'year',
        'bill_date',
        'status',
        'sp2d_date',
        'sp2d_number',
        'description',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bill_date' => 'date',
        'sp2d_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function ($bill) {
            $bill->budgetCategory->updateRealization();
            Cache::tags(['budget_stats'])->flush();
        });

        static::deleted(function ($bill) {
            $bill->budgetCategory->updateRealization();
            Cache::tags(['budget_stats'])->flush();
        });
    }

    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $months[$this->month] ?? '';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Pending</span>',
            'sp2d' => '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">SP2D</span>',
            'cancelled' => '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Dibatalkan</span>',
        ];

        return $badges[$this->status] ?? '';
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSP2D($query)
    {
        return $query->where('status', 'sp2d');
    }

    public function scopeByMonth($query, $month, $year = null)
    {
        $query = $query->where('month', $month);
        if ($year) {
            $query->where('year', $year);
        }
        return $query;
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }
}
