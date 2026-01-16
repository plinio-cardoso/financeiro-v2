<?php

namespace App\Models\Accessors;

trait TagAccessorTrait
{
    /**
     * Get tag color or default gray color
     */
    public function getColorWithDefault(): string
    {
        return $this->color ?? '#6B7280';
    }

    /**
     * Get count of transactions associated with this tag
     */
    public function getTransactionCount(): int
    {
        return $this->transactions()->count();
    }
}
