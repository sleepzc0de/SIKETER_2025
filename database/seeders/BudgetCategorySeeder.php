<?php
// database/seeders/BudgetCategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetCategory;

class BudgetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'kegiatan' => '054001',
                'kro_code' => '001',
                'ro_code' => '001',
                'initial_code' => '001',
                'account_code' => '524111',
                'program_kegiatan_output' => 'Dukungan Manajemen dan Pelaksanaan Tugas Teknis Lainnya pada Inspektorat Utama',
                'budget_allocation' => 500000000,
                'pic' => 'Kepala Kantor',
                'year' => 2025,
            ],
            [
                'kegiatan' => '054001',
                'kro_code' => '001',
                'ro_code' => '002',
                'initial_code' => '001',
                'account_code' => '524111',
                'program_kegiatan_output' => 'Layanan Perkantoran',
                'budget_allocation' => 300000000,
                'pic' => 'Kasubag TU',
                'year' => 2025,
            ],
            [
                'kegiatan' => '054002',
                'kro_code' => '001',
                'ro_code' => '001',
                'initial_code' => '001',
                'account_code' => '524111',
                'program_kegiatan_output' => 'Pengawasan Intern di Lingkungan Kemenko PMK',
                'budget_allocation' => 750000000,
                'pic' => 'Seksi Pengawasan I',
                'year' => 2025,
            ],
        ];

        foreach ($categories as $category) {
            BudgetCategory::firstOrCreate(
                [
                    'kegiatan' => $category['kegiatan'],
                    'kro_code' => $category['kro_code'],
                    'ro_code' => $category['ro_code'],
                    'initial_code' => $category['initial_code'],
                    'account_code' => $category['account_code'],
                    'year' => $category['year'],
                ],
                $category
            );
        }

        $this->command->info('Budget categories seeded successfully.');
    }
}
