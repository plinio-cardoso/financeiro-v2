<?php

namespace App\Models\Actions;

use App\Enums\TransactionStatusEnum;

trait TransactionActionTrait
{
    /**
     * Mark transaction as paid
     */
    public function markAsPaid(): void
    {
        $this->status = TransactionStatusEnum::Paid;
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Mark transaction as pending
     */
    public function markAsPending(): void
    {
        $this->status = TransactionStatusEnum::Pending;
        $this->paid_at = null;
        $this->save();
    }
}
