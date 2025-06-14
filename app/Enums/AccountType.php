<?php

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case CreditCard = 'credit_card';
    case Cash = 'cash';
}
