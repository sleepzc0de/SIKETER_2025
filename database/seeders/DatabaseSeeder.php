<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BudgetCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan', 'guard_name' => 'web']);
        $ppkRole = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $tuRole = Role::firstOrCreate(['name' => 'tu', 'guard_name' => 'web']);

        // Assign permissions to roles
        $adminRole->syncPermissions(Permission::all());

        $pimpinanRole->syncPermissions([
            'view dashboard',
            'view budget',
            'edit budget',
            'view bills',
            'approve bills',
            'view reports',
            'export data',
        ]);

        $ppkRole->syncPermissions([
            'view dashboard',
            'view budget',
            'view bills',
            'create bills',
            'edit bills',
        ]);

        $staffRole->syncPermissions([
            'view dashboard',
            'view budget',
            'view bills',
        ]);

        $tuRole->syncPermissions([
            'view dashboard',
            'view budget',
            'view bills',
        ]);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@kemenkeu.go.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($adminRole);

        // Create pimpinan user
        $pimpinan = User::firstOrCreate(
            ['email' => 'pimpinan@kemenkeu.go.id'],
            [
                'name' => 'Pimpinan BMN',
                'password' => Hash::make('pimpinan123'),
                'role' => 'pimpinan',
                'email_verified_at' => now(),
            ]
        );
        $pimpinan->assignRole($pimpinanRole);

        // Create PPK user
        $ppk = User::firstOrCreate(
            ['email' => 'ppk@kemenkeu.go.id'],
            [
                'name' => 'PPK Keuangan',
                'password' => Hash::make('ppk123'),
                'role' => 'ppk',
                'email_verified_at' => now(),
            ]
        );
        $ppk->assignRole($ppkRole);

        // Create staff user
        $staff = User::firstOrCreate(
            ['email' => 'staff@kemenkeu.go.id'],
            [
                'name' => 'Staff Keuangan',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );
        $staff->assignRole($staffRole);

        // Create sample budget data
        $budgetData = [
            [
                'kegiatan' => '4753',
                'kro_code' => '4753',
                'ro_code' => 'AAH',
                'initial_code' => 'ROMADAN1',
                'account_code' => '053',
                'program_kegiatan_output' => 'Peraturan/kebijakan terkait pengelolaan BMN',
                'pic' => 'ROMADAN',
                'budget_allocation' => 275398000.00,
                'reference' => '4753AAH053ROMADAN1ROMADAN1',
                'reference2' => '4753AAH053ROMADAN1',
                'reference_output' => '4753AAH053',
                'length' => 26,
                'sisa_anggaran' => 275398000.00,
            ],
            [
                'kegiatan' => '4753',
                'kro_code' => '4753',
                'ro_code' => 'AAH',
                'initial_code' => 'A',
                'account_code' => '053',
                'program_kegiatan_output' => 'Penyusunan Strategi dan Kebijakan Pengelolaan BMN dan Pengadaan',
                'pic' => 'Persija',
                'budget_allocation' => 174327000.00,
                'reference' => '4753AAH053AA',
                'reference2' => '4753AAH053A',
                'reference_output' => '4753AAH053',
                'length' => 12,
                'sisa_anggaran' => 174327000.00,
            ],
            [
                'kegiatan' => '4753',
                'kro_code' => '4753',
                'ro_code' => 'EBA',
                'initial_code' => 'ROMADAN2',
                'account_code' => '401',
                'program_kegiatan_output' => 'Rencana Kebutuhan BMN dan Pengelolaannya Tingkat Kementerian',
                'pic' => 'ROMADAN',
                'budget_allocation' => 970514000.00,
                'reference' => '4753EBA401ROMADAN2ROMADAN2',
                'reference2' => '4753EBA401ROMADAN2',
                'reference_output' => '4753EBA401',
                'length' => 26,
                'sisa_anggaran' => 970514000.00,
            ],
            [
                'kegiatan' => '4753',
                'kro_code' => '4753',
                'ro_code' => 'EBA',
                'initial_code' => 'ROMADAN3',
                'account_code' => '403',
                'program_kegiatan_output' => 'Layanan Pengadaan',
                'pic' => 'ROMADAN',
                'budget_allocation' => 575302000.00,
                'reference' => '4753EBA403ROMADAN3ROMADAN3',
                'reference2' => '4753EBA403ROMADAN3',
                'reference_output' => '4753EBA403',
                'length' => 26,
                'sisa_anggaran' => 575302000.00,
            ],
            [
                'kegiatan' => '4753',
                'kro_code' => '4753',
                'ro_code' => 'EBA',
                'initial_code' => 'Kerumahtanggaan',
                'account_code' => '405',
                'program_kegiatan_output' => 'Layanan Kerumahtanggaan',
                'pic' => 'ROMADAN',
                'budget_allocation' => 520814000.00,
                'reference' => '4753EBA405KerumahtanggaanROMADAN4',
                'reference2' => '4753EBA405Kerumahtanggaan',
                'reference_output' => '4753EBA405',
                'length' => 33,
                'sisa_anggaran' => 520814000.00,
            ],
        ];

        foreach ($budgetData as $budget) {
            BudgetCategory::firstOrCreate(
                [
                    'kegiatan' => $budget['kegiatan'],
                    'kro_code' => $budget['kro_code'],
                    'ro_code' => $budget['ro_code'],
                    'initial_code' => $budget['initial_code'],
                    'account_code' => $budget['account_code'],
                ],
                $budget
            );
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
