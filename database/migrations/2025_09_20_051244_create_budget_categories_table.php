<?php
// database/migrations/2024_01_01_000002_create_budget_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('kro_code');
            $table->string('ro_code');
            $table->string('initial_code');
            $table->string('account_code');
            $table->text('description');
            $table->string('pic');
            $table->decimal('budget_allocation', 15, 2);
            $table->string('reference');
            $table->string('reference2')->nullable();
            $table->string('reference_output')->nullable();
            $table->integer('length');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_categories');
    }
};
