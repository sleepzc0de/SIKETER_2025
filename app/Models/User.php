<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'is_active',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Helper methods with caching for performance
    public function isAdmin(): bool
    {
        return Cache::remember("user_{$this->id}_is_admin", 1800, function () {
            return $this->role === 'admin' || $this->hasRole('admin');
        });
    }

    public function isPimpinan(): bool
    {
        return Cache::remember("user_{$this->id}_is_pimpinan", 1800, function () {
            return $this->role === 'pimpinan' || $this->hasRole('pimpinan');
        });
    }

    public function isPPK(): bool
    {
        return Cache::remember("user_{$this->id}_is_ppk", 1800, function () {
            return $this->role === 'ppk' || $this->hasRole('ppk');
        });
    }

    public function canApprove(): bool
    {
        return Cache::remember("user_{$this->id}_can_approve", 1800, function () {
            return $this->isAdmin() || $this->isPimpinan() || $this->hasPermissionTo('approve bills');
        });
    }

    public function canManageBudget(): bool
    {
        return Cache::remember("user_{$this->id}_can_manage_budget", 1800, function () {
            return $this->isAdmin() || $this->isPimpinan() || $this->hasPermissionTo('manage budget');
        });
    }

    public function canInputBills(): bool
    {
        return Cache::remember("user_{$this->id}_can_input_bills", 1800, function () {
            return $this->isAdmin() || $this->isPimpinan() || $this->isPPK() || $this->hasPermissionTo('create bills');
        });
    }

    public function canManageUsers(): bool
    {
        return Cache::remember("user_{$this->id}_can_manage_users", 1800, function () {
            return $this->isAdmin() || $this->hasPermissionTo('manage users');
        });
    }

    public function canViewReports(): bool
    {
        return Cache::remember("user_{$this->id}_can_view_reports", 1800, function () {
            return $this->hasPermissionTo('view reports');
        });
    }

    public function canExportData(): bool
    {
        return Cache::remember("user_{$this->id}_can_export_data", 1800, function () {
            return $this->hasPermissionTo('export data');
        });
    }

    // Safe permission check
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        try {
            return parent::hasPermissionTo($permission, $guardName);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Safe role check
    public function hasRole($roles, $guard = null): bool
    {
        try {
            return parent::hasRole($roles, $guard);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getRoleDisplayNameAttribute(): string
    {
        $primaryRole = $this->roles()->first();
        if ($primaryRole) {
            return Cache::get("role_meta_{$primaryRole->id}.display_name", ucfirst($primaryRole->name));
        }
        return ucfirst($this->role);
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active
            ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>'
            : '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Inactive</span>';
    }

    // Relationships
    public function budgetCategories()
    {
        return $this->hasMany(BudgetCategory::class, 'pic', 'name');
    }

    public function createdBills()
    {
        return $this->hasMany(Bill::class, 'created_by');
    }

    public function approvedBills()
    {
        return $this->hasMany(Bill::class, 'approved_by');
    }

    // Clear user cache on model events
    protected static function booted()
    {
        static::saved(function ($user) {
            $user->clearUserCache();
        });

        static::deleted(function ($user) {
            $user->clearUserCache();
        });
    }

    private function clearUserCache()
    {
        $cacheKeys = [
            "user_{$this->id}_is_admin",
            "user_{$this->id}_is_pimpinan",
            "user_{$this->id}_is_ppk",
            "user_{$this->id}_can_approve",
            "user_{$this->id}_can_manage_budget",
            "user_{$this->id}_can_input_bills",
            "user_{$this->id}_can_manage_users",
            "user_{$this->id}_can_view_reports",
            "user_{$this->id}_can_export_data",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
