<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
