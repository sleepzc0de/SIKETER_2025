<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage users');
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = ['admin', 'pimpinan', 'staff', 'tu', 'ppk'];

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,pimpinan,staff,tu,ppk',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        $user = User::create($validated);

        // Assign role-based permissions
        $this->assignRolePermissions($user, $validated['role']);

        // Clear user cache
        Cache::forget("user_{$user->id}_can_approve");
        Cache::forget("user_{$user->id}_can_manage_budget");
        Cache::forget("user_{$user->id}_can_input_bills");

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        $user->load('roles', 'permissions');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,pimpinan,staff,tu,ppk',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Update role-based permissions
        $this->assignRolePermissions($user, $validated['role']);

        // Clear user cache
        $this->clearUserCache($user);

        return redirect()->route('users.show', $user)->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();
        $this->clearUserCache($user);

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $this->clearUserCache($user);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "User berhasil {$status}.");
    }

    private function assignRolePermissions(User $user, string $role)
    {
        // Remove existing roles and permissions
        $user->roles()->detach();
        $user->permissions()->detach();

        // Assign role
        $roleModel = Role::firstOrCreate(['name' => $role]);
        $user->assignRole($roleModel);

        // Assign permissions based on role
        $permissions = $this->getRolePermissions($role);
        foreach ($permissions as $permission) {
            $permissionModel = Permission::firstOrCreate(['name' => $permission]);
            $user->givePermissionTo($permissionModel);
        }
    }

    private function getRolePermissions(string $role): array
    {
        $permissions = [
            'admin' => [
                'view dashboard',
                'manage users',
                'manage budget',
                'view budget',
                'create budget',
                'edit budget',
                'delete budget',
                'manage bills',
                'view bills',
                'create bills',
                'edit bills',
                'delete bills',
                'approve bills',
                'view reports',
                'export data',
            ],
            'pimpinan' => [
                'view dashboard',
                'view budget',
                'edit budget',
                'view bills',
                'approve bills',
                'view reports',
                'export data',
            ],
            'ppk' => [
                'view dashboard',
                'view budget',
                'view bills',
                'create bills',
                'edit bills',
            ],
            'staff' => [
                'view dashboard',
                'view budget',
                'view bills',
            ],
            'tu' => [
                'view dashboard',
                'view budget',
                'view bills',
            ],
        ];

        return $permissions[$role] ?? [];
    }

    private function clearUserCache(User $user)
    {
        Cache::forget("user_{$user->id}_is_admin");
        Cache::forget("user_{$user->id}_is_pimpinan");
        Cache::forget("user_{$user->id}_is_ppk");
        Cache::forget("user_{$user->id}_can_approve");
        Cache::forget("user_{$user->id}_can_manage_budget");
        Cache::forget("user_{$user->id}_can_input_bills");
    }
}
