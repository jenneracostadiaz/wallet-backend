<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_column(AccountType::cases(), 'value')),
            'balance' => 'required|numeric',
            'currency' => 'required|exists:currencies,id',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'type.required' => 'The type field is required.',
            'type.string' => 'The type must be a string.',
            'type.in' => 'The selected type is invalid.',
            'balance.required' => 'The balance field is required.',
            'balance.numeric' => 'The balance must be a number.',
            'currency_id.required' => 'The currency field is required.',
            'currency_id.exists' => 'The selected currency does not exist.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 255 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'name',
            'type' => 'type',
            'balance' => 'balance',
            'currency_id' => 'currency',
            'description' => 'description',
        ];

    }
}
