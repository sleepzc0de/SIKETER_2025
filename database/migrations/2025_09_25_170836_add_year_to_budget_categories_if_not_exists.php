<?php
// php artisan make:migration add_year_to_budget_categories_if_not_exists

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('budget_categories', 'year')) {
                $table->integer('year')->default(2025)->after('pic');
                $table->index('year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('budget_categories', function (Blueprint $table) {
            if (Schema::hasColumn('budget_categories', 'year')) {
                $table->dropColumn('year');
            }
        });
    }
};
