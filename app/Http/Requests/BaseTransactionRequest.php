<?php

namespace App\Http\Requests;

use App\Models\Account;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $account = Account::query()->find($this->input('account_id'));
        $category = Category::query()->find($this->input('category_id'));

        if (! $account || ! $category) {
            return true;
        }

        return $account->user_id === $user->id && $category->user_id === $user->id;
    }
}
