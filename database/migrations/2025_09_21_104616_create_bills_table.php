<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_category_id')->constrained('budget_categories')->onDelete('cascade');
            $table->string('bill_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->integer('month');
            $table->integer('year');
            $table->date('bill_date');
            $table->enum('status', ['pending', 'sp2d', 'cancelled'])->default('pending');
            $table->date('sp2d_date')->nullable();
            $table->string('sp2d_number')->nullable();
            $table->text('description');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'month', 'year']);
            $table->index('created_by');
            $table->index('budget_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
