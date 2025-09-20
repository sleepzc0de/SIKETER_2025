<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BudgetCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@kemenkeu.go.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create pimpinan user
        User::factory()->create([
            'name' => 'Pimpinan BMN',
            'email' => 'pimpinan@kemenkeu.go.id',
            'password' => Hash::make('pimpinan123'),
            'role' => 'pimpinan',
            'email_verified_at' => now(),
        ]);

        // Create staff user
        User::factory()->create([
            'name' => 'Staff Keuangan',
            'email' => 'staff@kemenkeu.go.id',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        // Create sample budget data based on your Excel file
        $budgetData = [
            [
                'kro_code' => '4753',
                'ro_code' => 'AAH',
                'initial_code' => 'ROMADAN1',
                'account_code' => '053',
                'description' => 'Peraturan/kebijakan terkait pengelolaan BMN',
                'pic' => 'ROMADAN',
                'budget_allocation' => 275398000,
                'reference' => '4753AAH053ROMADAN1ROMADAN1',
                'reference2' => '4753AAH053ROMADAN1',
                'reference_output' => '4753AAH053',
                'length' => 26,
            ],
            [
                'kro_code' => '4753',
                'ro_code' => 'AAH',
                'initial_code' => 'A',
                'account_code' => '053',
                'description' => 'Penyusunan Strategi dan Kebijakan Pengelolaan BMN dan Pengadaan',
                'pic' => 'Persija',
                'budget_allocation' => 174327000,
                'reference' => '4753AAH053AA',
                'reference2' => '4753AAH053A',
                'reference_output' => '4753AAH053',
                'length' => 12,
            ],
            [
                'kro_code' => '4753',
                'ro_code' => 'EBA',
                'initial_code' => 'ROMADAN2',
                'account_code' => '401',
                'description' => 'Rencana Kebutuhan BMN dan Pengelolaannya Tingkat Kementerian',
                'pic' => 'ROMADAN',
                'budget_allocation' => 970514000,
                'reference' => '4753EBA401ROMADAN2ROMADAN2',
                'reference2' => '4753EBA401ROMADAN2',
                'reference_output' => '4753EBA401',
                'length' => 26,
            ],
            [
                'kro_code' => '4753',
                'ro_code' => 'EBA',
                'initial_code' => 'ROMADAN3',
                'account_code' => '403',
                'description' => 'Layanan Pengadaan',
                'pic' => 'ROMADAN',
                'budget_allocation' => 575302000,
                'reference' => '4753EBA403ROMADAN3ROMADAN3',
                'reference2' => '4753EBA403ROMADAN3',
                'reference_output' => '4753EBA403',
                'length' => 26,
            ],
            [
                'kro_code' => '4753',
                'ro_code' => 'EBA',
                'initial_code' => 'Kerumahtanggaan',
                'account_code' => '405',
                'description' => 'Layanan Kerumahtanggaan',
                'pic' => 'ROMADAN',
                'budget_allocation' => 520814000,
                'reference' => '4753EBA405KerumahtanggaanROMADAN4',
                'reference2' => '4753EBA405Kerumahtanggaan',
                'reference_output' => '4753EBA405',
                'length' => 33,
            ],
        ];

        foreach ($budgetData as $budget) {
            BudgetCategory::create($budget);
        }
    }
}
