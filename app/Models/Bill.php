<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'budget_category_id',
        'amount',
        'description',
        'status',
        'sp2d_number',
        'sp2d_date',
        'approved_at',
        'approved_by',
        'created_by',
        'month',
        'year',
        'due_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sp2d_date' => 'date',
        'approved_at' => 'datetime',
        'due_date' => 'date',
        'month' => 'integer',
        'year' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            // Auto-generate bill number if not provided
            if (!$bill->bill_number) {
                $bill->bill_number = 'BILL-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }

            // Set month and year from created_at if not provided
            if (!$bill->month) {
                $bill->month = Carbon::now()->month;
            }
            if (!$bill->year) {
                $bill->year = Carbon::now()->year;
            }
        });

        static::updated(function ($bill) {
            // Update budget category realization when bill status changes
            if ($bill->isDirty('status') && $bill->budgetCategory) {
                $bill->budgetCategory->updateRealization();
            }
        });
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'sp2d');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Menunggu Persetujuan',
            'sp2d' => 'Sudah SP2D',
            'cancelled' => 'Dibatalkan'
        ][$this->status] ?? 'Unknown';
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'yellow',
            'sp2d' => 'green',
            'cancelled' => 'red'
        ][$this->status] ?? 'gray';
    }

    public function getMonthNameAttribute()
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $monthNames[$this->month] ?? 'Unknown';
    }
}
