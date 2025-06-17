<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;

readonly class DashboardService
{
    public function __construct(private int $userId) {}
}
