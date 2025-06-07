<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:income,expense,transfer',
            'parent_id' => 'nullable',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7', // Assuming color is a hex code
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
            'parent_id.exists' => 'The selected parent category does not exist.',
            'icon.string' => 'The icon must be a string.',
            'icon.max' => 'The icon may not be greater than 255 characters.',
            'color.string' => 'The color must be a string.',
            'color.max' => 'The color may not be greater than 7 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'name',
            'type' => 'type',
            'parent_id' => 'parent category',
            'icon' => 'icon',
            'color' => 'color',
        ];
    }
}
