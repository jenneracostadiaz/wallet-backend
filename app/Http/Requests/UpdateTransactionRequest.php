<?php

namespace App\Http\Requests;

use App\Models\Account;
use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTransactionRequest extends FormRequest
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

    public function rules(): array
    {
        return [
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'account_id' => [
                'required',
                'exists:accounts,id',
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = Category::query()->find($value);
                    if ($category && $category->type !== $this->input('type')) {
                        $fail('The category type must match the transaction type.');
                    }
                },
            ],
            'date' => 'required|date_format:Y-m-d H:i:s',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'The transaction type is required.',
            'type.in' => 'The transaction type must be either income or expense.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'account_id.required' => 'The account is required.',
            'account_id.exists' => 'The selected account does not exist.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'date.date_format' => 'The date must be in the format Y-m-d H:i:s.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 255 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
