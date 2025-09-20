<?php
// database/migrations/2024_01_01_000003_create_budget_realizations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_realizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_category_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('amount', 15, 2);
            $table->decimal('outstanding_bills', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_realizations');
    }
};
