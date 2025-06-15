<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class TransactionQueryBuilder extends Builder
{
    public function forUser($userId): self
    {
        return $this->where('user_id', $userId);
    }

    public function ofType($types): self
    {
        $types = is_array($types) ? $types : [$types];

        return $this->whereIn('type', $types);
    }

    public function inCategory($categoryId): self
    {
        return $this->where('category_id', $categoryId);
    }

    public function fromAccount($accountId): self
    {
        return $this->where('account_id', $accountId);
    }

    public function betweenDates($from, $to = null): self
    {
        if ($from) {
            $this->whereDate('date', '>=', $from);
        }

        if ($to) {
            $this->whereDate('date', '<=', $to);
        }

        return $this;
    }

    public function withRelations(): self
    {
        return $this->with(['account.currency', 'category']);
    }
}
