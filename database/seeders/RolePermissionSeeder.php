<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $this->createPermissions();

        // Create roles
        $this->createRoles();

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Assign roles to existing users
        $this->assignRolesToUsers();
    }

    private function createPermissions()
    {
        $permissions = [
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

        foreach ($permissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => 'web']
            );

            // Store metadata in cache
            Cache::put("permission_meta_{$permission->id}", [
                'display_name' => $permissionData['display_name'],
                'description' => '',
                'group' => $permissionData['group'],
            ], now()->addDays(30));
        }
    }

    private function createRoles()
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access with all permissions'],
            ['name' => 'pimpinan', 'display_name' => 'Pimpinan', 'description' => 'Leadership role with approval permissions'],
            ['name' => 'ppk', 'display_name' => 'Pejabat Pembuat Komitmen', 'description' => 'Budget commitment officer with bills management'],
            ['name' => 'staff', 'display_name' => 'Staff', 'description' => 'Regular staff with limited access'],
            ['name' => 'tu', 'display_name' => 'Tata Usaha', 'description' => 'Administrative staff role'],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => 'web'
            ]);

            // Store metadata in cache
            Cache::put("role_meta_{$role->id}", [
                'display_name' => $roleData['display_name'],
                'description' => $roleData['description'],
            ], now()->addDays(30));
        }
    }

    private function assignPermissionsToRoles()
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
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permissionModels = Permission::whereIn('name', $permissions)->get();
                $role->syncPermissions($permissionModels);
            }
        }
    }

    private function assignRolesToUsers()
    {
        // Assign admin role to first user or users with admin role
        $adminUsers = User::where('role', 'admin')->get();
        $adminRole = Role::where('name', 'admin')->first();

        foreach ($adminUsers as $user) {
            $user->assignRole($adminRole);
        }

        // Assign roles to other users based on their role field
        $roleMapping = ['pimpinan', 'ppk', 'staff', 'tu'];

        foreach ($roleMapping as $roleName) {
            $users = User::where('role', $roleName)->get();
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                foreach ($users as $user) {
                    $user->assignRole($role);
                }
            }
        }
    }
}
