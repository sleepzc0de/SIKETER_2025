<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    // =============== ROLES MANAGEMENT ===============

    public function rolesIndex(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role.');
        }

        $query = Role::withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        $roles = $query->orderBy('name')->paginate(20);

        return view('roles.index', compact('roles'));
    }

    public function rolesCreate()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role.');
        }

        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        });

        return view('roles.create', compact('permissions'));
    }

    public function rolesStore(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            // Store additional attributes in cache or separate table if needed
            Cache::put("role_meta_{$role->id}", [
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? '',
            ], now()->addDays(30));

            if (!empty($validated['permissions'])) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                $role->syncPermissions($permissions);
            }
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function rolesShow($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role.');
        }

        $role = Role::with(['permissions', 'users'])->findOrFail($id);
        $roleMeta = Cache::get("role_meta_{$role->id}", [
            'display_name' => ucfirst($role->name),
            'description' => '',
        ]);

        return view('roles.show', compact('role', 'roleMeta'));
    }

    public function rolesEdit($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role.');
        }

        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        });

        $roleMeta = Cache::get("role_meta_{$role->id}", [
            'display_name' => ucfirst($role->name),
            'description' => '',
        ]);

        return view('roles.edit', compact('role', 'permissions', 'roleMeta'));
    }

    public function rolesUpdate(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role.');
        }

        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::transaction(function () use ($role, $validated) {
            $role->update(['name' => $validated['name']]);

            // Update metadata in cache
            Cache::put("role_meta_{$role->id}", [
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? '',
            ], now()->addDays(30));

            if (isset($validated['permissions'])) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.show', $role->id)->with('success', 'Role berhasil diperbarui.');
    }

    public function rolesDestroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role.');
        }

        $role = Role::findOrFail($id);

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user.');
        }

        DB::transaction(function () use ($role) {
            Cache::forget("role_meta_{$role->id}");
            $role->delete();
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }

    // =============== PERMISSIONS MANAGEMENT ===============

    public function permissionsIndex(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola permission.');
        }

        $query = Permission::withCount('roles');

        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        if ($request->filled('group')) {
            $query->where('name', 'LIKE', '%' . $request->group . '%');
        }

        $permissions = $query->orderBy('name')->paginate(20);

        // Get permission groups
        $groups = Permission::all()->map(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        })->unique()->sort()->values();

        return view('permissions.index', compact('permissions', 'groups'));
    }

    public function permissionsCreate()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola permission.');
        }

        $groups = Permission::all()->map(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        })->unique()->sort()->values();

        return view('permissions.create', compact('groups'));
    }

    public function permissionsStore(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola permission.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            // Store additional attributes in cache
            Cache::put("permission_meta_{$permission->id}", [
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? '',
                'group' => $validated['group'],
            ], now()->addDays(30));
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Permission berhasil ditambahkan.');
    }

    public function permissionsShow($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola permission.');
        }

        $permission = Permission::with('roles')->findOrFail($id);
        $permissionMeta = Cache::get("permission_meta_{$permission->id}", [
            'display_name' => ucfirst($permission->name),
            'description' => '',
            'group' => 'general',
        ]);

        return view('permissions.show', compact('permission', 'permissionMeta'));
    }

    public function permissionsEdit($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola permission.');
        }

        $permission = Permission::findOrFail($id);
        $groups = Permission::all()->map(function($perm) {
            return explode(' ', $perm->name)[1] ?? 'general';
        })->unique()->sort()->values();

        $permissionMeta = Cache::get("permission_meta_{$permission->id}", [
            'display_name' => ucfirst($permission->name),
            'description' => '',
            'group' => 'general',
        ]);

        return view('permissions.edit', compact('permission', 'groups', 'permissionMeta'));
    }

    public function permissionsUpdate(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola permission.');
        }

        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($permission, $validated) {
            $permission->update(['name' => $validated['name']]);

            // Update metadata in cache
            Cache::put("permission_meta_{$permission->id}", [
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? '',
                'group' => $validated['group'],
            ], now()->addDays(30));
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('permissions.show', $permission->id)->with('success', 'Permission berhasil diperbarui.');
    }

    public function permissionsDestroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola permission.');
        }

        $permission = Permission::findOrFail($id);

        // Check if permission has roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')->with('error', 'Permission tidak dapat dihapus karena masih digunakan oleh role.');
        }

        DB::transaction(function () use ($permission) {
            Cache::forget("permission_meta_{$permission->id}");
            $permission->delete();
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Permission berhasil dihapus.');
    }

    // =============== USER ROLE ASSIGNMENT ===============

    public function userRoles(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role user.');
        }

        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderBy('name')->paginate(20);
        $roles = Role::orderBy('name')->get();

        return view('role-permissions.user-roles', compact('users', 'roles'));
    }

    public function assignUserRole(Request $request, $userId)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role user.');
        }

        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () use ($user, $validated) {
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);

            // Update user role field for compatibility
            if ($roles->isNotEmpty()) {
                $user->update(['role' => $roles->first()->name]);
            }
        });

        return redirect()->route('role-permissions.user-roles')->with('success', 'Role user berhasil diperbarui.');
    }

    public function removeUserRole($userId, $roleId)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role user.');
        }

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($roleId);

        DB::transaction(function () use ($user, $role) {
            $user->removeRole($role);

            // Update user role field if no roles left
            if ($user->roles()->count() === 0) {
                $user->update(['role' => 'staff']);
            } else {
                $user->update(['role' => $user->roles()->first()->name]);
            }
        });

        return redirect()->route('role-permissions.user-roles')->with('success', 'Role berhasil dihapus dari user.');
    }

    // =============== BULK OPERATIONS ===============

    public function bulkAssignRole(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola role user.');
        }

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::findOrFail($validated['role_id']);
            $users = User::whereIn('id', $validated['user_ids'])->get();

            foreach ($users as $user) {
                $user->assignRole($role);
                $user->update(['role' => $role->name]);
            }
        });

        return redirect()->route('role-permissions.user-roles')->with('success', 'Role berhasil diberikan ke multiple user.');
    }

    public function syncDefaultPermissions()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses untuk sinkronisasi permission.');
        }

        DB::transaction(function () {
            $this->createDefaultPermissions();
            $this->syncRolePermissions();
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->back()->with('success', 'Default permissions berhasil disinkronisasi.');
    }

    private function createDefaultPermissions()
    {
        $defaultPermissions = [
            // Dashboard
            ['name' => 'view dashboard', 'group' => 'dashboard', 'display_name' => 'Lihat Dashboard'],

            // User Management
            ['name' => 'view users', 'group' => 'users', 'display_name' => 'Lihat User'],
            ['name' => 'create users', 'group' => 'users', 'display_name' => 'Tambah User'],
            ['name' => 'edit users', 'group' => 'users', 'display_name' => 'Edit User'],
            ['name' => 'delete users', 'group' => 'users', 'display_name' => 'Hapus User'],
            ['name' => 'manage users', 'group' => 'users', 'display_name' => 'Kelola User'],

            // Budget Management
            ['name' => 'view budget', 'group' => 'budget', 'display_name' => 'Lihat Anggaran'],
            ['name' => 'create budget', 'group' => 'budget', 'display_name' => 'Tambah Anggaran'],
            ['name' => 'edit budget', 'group' => 'budget', 'display_name' => 'Edit Anggaran'],
            ['name' => 'delete budget', 'group' => 'budget', 'display_name' => 'Hapus Anggaran'],
            ['name' => 'manage budget', 'group' => 'budget', 'display_name' => 'Kelola Anggaran'],

            // Bills Management
            ['name' => 'view bills', 'group' => 'bills', 'display_name' => 'Lihat Tagihan'],
            ['name' => 'create bills', 'group' => 'bills', 'display_name' => 'Tambah Tagihan'],
            ['name' => 'edit bills', 'group' => 'bills', 'display_name' => 'Edit Tagihan'],
            ['name' => 'delete bills', 'group' => 'bills', 'display_name' => 'Hapus Tagihan'],
            ['name' => 'approve bills', 'group' => 'bills', 'display_name' => 'Approve Tagihan'],
            ['name' => 'manage bills', 'group' => 'bills', 'display_name' => 'Kelola Tagihan'],

            // Reports
            ['name' => 'view reports', 'group' => 'reports', 'display_name' => 'Lihat Laporan'],
            ['name' => 'export reports', 'group' => 'reports', 'display_name' => 'Export Laporan'],

            // Role & Permissions
            ['name' => 'view roles', 'group' => 'roles', 'display_name' => 'Lihat Role'],
            ['name' => 'create roles', 'group' => 'roles', 'display_name' => 'Tambah Role'],
            ['name' => 'edit roles', 'group' => 'roles', 'display_name' => 'Edit Role'],
            ['name' => 'delete roles', 'group' => 'roles', 'display_name' => 'Hapus Role'],
            ['name' => 'view permissions', 'group' => 'permissions', 'display_name' => 'Lihat Permission'],
            ['name' => 'create permissions', 'group' => 'permissions', 'display_name' => 'Tambah Permission'],
            ['name' => 'edit permissions', 'group' => 'permissions', 'display_name' => 'Edit Permission'],
            ['name' => 'delete permissions', 'group' => 'permissions', 'display_name' => 'Hapus Permission'],

            // Settings
            ['name' => 'view settings', 'group' => 'settings', 'display_name' => 'Lihat Pengaturan'],
            ['name' => 'edit settings', 'group' => 'settings', 'display_name' => 'Edit Pengaturan'],

            // Export Data
            ['name' => 'export data', 'group' => 'export', 'display_name' => 'Export Data'],
        ];

        foreach ($defaultPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => 'web']
            );

            Cache::put("permission_meta_{$permission->id}", [
                'display_name' => $permissionData['display_name'],
                'description' => '',
                'group' => $permissionData['group'],
            ], now()->addDays(30));
        }
    }

    private function syncRolePermissions()
    {
        $rolePermissions = [
            'admin' => [
                'view dashboard', 'manage users', 'view users', 'create users', 'edit users', 'delete users',
                'manage budget', 'view budget', 'create budget', 'edit budget', 'delete budget',
                'manage bills', 'view bills', 'create bills', 'edit bills', 'delete bills', 'approve bills',
                'view reports', 'export reports', 'view roles', 'create roles', 'edit roles', 'delete roles',
                'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
                'view settings', 'edit settings', 'export data'
            ],
            'pimpinan' => [
                'view dashboard', 'view budget', 'edit budget', 'view bills', 'approve bills',
                'view reports', 'export reports', 'export data'
            ],
            'ppk' => [
                'view dashboard', 'view budget', 'view bills', 'create bills', 'edit bills', 'view reports'
            ],
            'staff' => [
                'view dashboard', 'view budget', 'view bills', 'view reports'
            ],
            'tu' => [
                'view dashboard', 'view budget', 'view bills', 'view reports'
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            Cache::put("role_meta_{$role->id}", [
                'display_name' => ucfirst($roleName),
                'description' => "Default {$roleName} role",
            ], now()->addDays(30));

            $permissionModels = Permission::whereIn('name', $permissions)->get();
            $role->syncPermissions($permissionModels);
        }
    }
}
