<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('type')->default('debit');
            $table->string('status')->default('pending');
            $table->date('due_date');
            $table->datetime('paid_at')->nullable();
            $table->timestamps();

            // Indexes for optimization
            $table->index('user_id');
            $table->index('due_date');
            $table->index('status');
            $table->index('type');
            $table->index(['user_id', 'due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
