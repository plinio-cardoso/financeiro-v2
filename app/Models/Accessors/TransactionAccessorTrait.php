<?php

namespace App\Models\Accessors;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;

trait TransactionAccessorTrait
{
    /**
     * Get formatted amount in Brazilian currency format
     */
    public function getFormattedAmount(): string
    {
        return 'R$ '.number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get formatted due date (dd/mm/yyyy)
     */
    public function getFormattedDueDate(): string
    {
        return $this->due_date->format('d/m/Y');
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatusEnum::Pending;
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
    {
        return $this->status === TransactionStatusEnum::Paid;
    }

    /**
     * Check if transaction is overdue (past due date and still pending)
     */
    public function isOverdue(): bool
    {
        return $this->isPending() && $this->due_date->isPast();
    }

    /**
     * Get days until due date (negative if overdue)
     */
    public function getDaysUntilDue(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Check if transaction is a debit
     */
    public function isDebit(): bool
    {
        return $this->type === TransactionTypeEnum::Debit;
    }

    /**
     * Check if transaction is a credit
     */
    public function isCredit(): bool
    {
        return $this->type === TransactionTypeEnum::Credit;
    }
}
