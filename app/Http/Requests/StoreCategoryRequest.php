<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:expense,income,transfer',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.string' => 'El nombre de la categoría debe ser una cadena de texto.',
            'name.max' => 'El nombre de la categoría no debe exceder 255 caracteres.',
            'type.required' => 'El tipo de categoría es obligatorio.',
            'type.string' => 'El tipo de categoría debe ser una cadena de texto.',
            'type.in' => 'El tipo de categoría debe ser uno de: expense, income, transfer.',
            'icon.string' => 'El icono debe ser una cadena de texto.',
            'icon.max' => 'El icono no debe exceder 255 caracteres.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
            'user_id.required' => 'El ID de usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
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
