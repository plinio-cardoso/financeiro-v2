<?php

namespace App\Enums;

enum RecurringFrequencyEnum: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Custom = 'custom';
}
