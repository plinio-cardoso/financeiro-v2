<?php

namespace App\Enums;

enum TransactionStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
}
