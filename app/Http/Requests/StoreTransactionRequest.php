<?php

namespace App\Http\Requests;

use App\Models\Account;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransactionRequest extends BaseTransactionRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'account_id' => [
                'required',
                'exists:accounts,id',
            ],
            'to_account_id' => [
                'required_if:type,transfer',
                'exclude_unless:type,transfer',
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    if ($this->input('type') === 'transfer') {
                        if ($value == $this->input('account_id')) {
                            $fail('The destination account must be different from the origin account.');
                        }
                        $from = Account::query()->find($this->input('account_id'));
                        $to = Account::query()->find($value);
                        if ($from && $to && $from->currency_id !== $to->currency_id) {
                            $fail('Transfers between accounts with different currencies are not allowed.');
                        }
                    }
                },
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

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
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
            'to_account_id.required_if' => 'The destination account is required for transfers.',
            'to_account_id.exclude_unless' => 'The destination account is required for transfers.',
            'to_account_id.exists' => 'The selected destination account does not exist.',
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
