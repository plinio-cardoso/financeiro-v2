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
        Schema::table('transactions', function (Blueprint $table) {
            // Composite index for common query patterns
            $table->index(['user_id', 'status', 'due_date'], 'idx_user_status_due_date');
            $table->index(['user_id', 'type', 'due_date'], 'idx_user_type_due_date');

            // Index for recurring transactions
            $table->index('recurring_transaction_id', 'idx_recurring_transaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_user_status_due_date');
            $table->dropIndex('idx_user_type_due_date');
            $table->dropIndex('idx_recurring_transaction');
        });
    }
};
