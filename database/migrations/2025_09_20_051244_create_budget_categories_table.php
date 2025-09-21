<?php
// database/migrations/2025_09_20_051244_create_budget_categories_table.php (Updated)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('kegiatan');
            $table->string('kro_code');
            $table->string('ro_code');
            $table->string('initial_code');
            $table->string('account_code');
            $table->text('program_kegiatan_output'); // Program/Kegiatan/Output/Suboutput/Komponen/Subkomp/Akun/Detil
            $table->string('pic');
            $table->decimal('budget_allocation', 15, 2);
            $table->string('reference');
            $table->string('reference2')->nullable();
            $table->string('reference_output')->nullable();
            $table->integer('length');

            // Realisasi per bulan
            $table->decimal('realisasi_jan', 15, 2)->default(0);
            $table->decimal('realisasi_feb', 15, 2)->default(0);
            $table->decimal('realisasi_mar', 15, 2)->default(0);
            $table->decimal('realisasi_apr', 15, 2)->default(0);
            $table->decimal('realisasi_mei', 15, 2)->default(0);
            $table->decimal('realisasi_jun', 15, 2)->default(0);
            $table->decimal('realisasi_jul', 15, 2)->default(0);
            $table->decimal('realisasi_agu', 15, 2)->default(0);
            $table->decimal('realisasi_sep', 15, 2)->default(0);
            $table->decimal('realisasi_okt', 15, 2)->default(0);
            $table->decimal('realisasi_nov', 15, 2)->default(0);
            $table->decimal('realisasi_des', 15, 2)->default(0);

            $table->decimal('tagihan_outstanding', 15, 2)->default(0);
            $table->decimal('total_penyerapan', 15, 2)->default(0);
            $table->decimal('sisa_anggaran', 15, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['kro_code', 'ro_code', 'initial_code', 'account_code']);
            $table->index('pic');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_categories');
    }
};
