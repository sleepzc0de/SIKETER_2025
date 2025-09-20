<?php
// app/Models/BudgetApproval.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_realization_id',
        'user_id',
        'action',
        'notes',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function budgetRealization()
    {
        return $this->belongsTo(BudgetRealization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
