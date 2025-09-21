<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role checking methods with caching
    public function isAdmin(): bool
    {
        return Cache::remember("user_{$this->id}_is_admin", 3600, function () {
            return $this->role === 'admin';
        });
    }

    public function isPimpinan(): bool
    {
        return Cache::remember("user_{$this->id}_is_pimpinan", 3600, function () {
            return $this->role === 'pimpinan';
        });
    }

    public function isPPK(): bool
    {
        return Cache::remember("user_{$this->id}_is_ppk", 3600, function () {
            return $this->role === 'ppk';
        });
    }

    public function canApprove(): bool
    {
        return Cache::remember("user_{$this->id}_can_approve", 3600, function () {
            return in_array($this->role, ['admin', 'pimpinan']);
        });
    }

    public function canManageBudget(): bool
    {
        return Cache::remember("user_{$this->id}_can_manage_budget", 3600, function () {
            return in_array($this->role, ['admin', 'pimpinan']);
        });
    }

    public function canInputBills(): bool
    {
        return Cache::remember("user_{$this->id}_can_input_bills", 3600, function () {
            return in_array($this->role, ['admin', 'pimpinan', 'ppk']);
        });
    }

    // Relationships
    public function budgetRealizations()
    {
        return $this->hasMany(BudgetRealization::class, 'created_by');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'created_by');
    }

    public function approvedBills()
    {
        return $this->hasMany(Bill::class, 'approved_by');
    }
}
