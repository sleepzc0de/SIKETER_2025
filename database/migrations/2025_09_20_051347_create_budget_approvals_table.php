<?php
// database/migrations/2024_01_01_000004_create_budget_approvals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_realization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['approved', 'rejected', 'pending']);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_approvals');
    }
};
