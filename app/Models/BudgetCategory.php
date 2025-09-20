<?php
// app/Models/BudgetCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'kro_code',
        'ro_code',
        'initial_code',
        'account_code',
        'description',
        'pic',
        'budget_allocation',
        'reference',
        'reference2',
        'reference_output',
        'length',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'budget_allocation' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function budgetRealizations()
    {
        return $this->hasMany(BudgetRealization::class);
    }

    public function getTotalRealizationAttribute()
    {
        return $this->budgetRealizations()->sum('amount');
    }

    public function getRemainingBudgetAttribute()
    {
        return $this->budget_allocation - $this->total_realization;
    }

    public function getRealizationPercentageAttribute()
    {
        if ($this->budget_allocation <= 0) return 0;
        return ($this->total_realization / $this->budget_allocation) * 100;
    }
}
