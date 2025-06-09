<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string|in:income,expense,transfer',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required_unless:type,transfer|exists:categories,id|nullable',
            'to_account_id' => 'required_if:type,transfer|exists:accounts,id|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 255 characters.',
            'date.required' => 'The date field is required.',
            'date.date' => 'The date must be a valid date.',
            'type.required' => 'The type field is required.',
            'type.string' => 'The type must be a string.',
            'type.in' => 'The selected type is invalid.',
            'account_id.required' => 'The account field is required.',
            'account_id.exists' => 'The selected account does not exist.',
            'category_id.required_unless' => 'The category field is required unless the transaction type is transfer.',
            'category_id.exists' => 'The selected category does not exist.',
            'to_account_id.required_if' => 'The destination account field is required for transfer transactions.',
            'to_account_id.exists' => 'The selected destination account does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'amount',
            'description' => 'description',
            'date' => 'date',
            'type' => 'transaction type',
            'account_id' => 'account',
            'category_id' => 'category',
            'to_account_id' => 'destination account',
        ];
    }
}
