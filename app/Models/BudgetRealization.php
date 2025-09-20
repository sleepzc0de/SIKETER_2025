<?php
// app/Models/BudgetRealization.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetRealization extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_category_id',
        'year',
        'month',
        'amount',
        'outstanding_bills',
        'description',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'outstanding_bills' => 'decimal:2',
        ];
    }

    public function budgetCategory()
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals()
    {
        return $this->hasMany(BudgetApproval::class);
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

    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->outstanding_bills;
    }
}
