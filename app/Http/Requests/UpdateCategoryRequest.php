<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:expense,income,transfer',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.string' => 'El nombre de la categoría debe ser una cadena de texto.',
            'name.max' => 'El nombre de la categoría no puede tener más de 255 caracteres.',
            'type.required' => 'El tipo de categoría es obligatorio.',
            'type.in' => 'El tipo de categoría seleccionado no es válido.',
            'type.string' => 'El tipo de categoría debe ser una cadena de texto.',
            'icon.string' => 'El icono debe ser una cadena de texto.',
            'icon.max' => 'El icono no puede tener más de 255 caracteres.',
            'icon.required' => 'El icono es obligatorio.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
            'parent_id.integer' => 'El ID de la categoría padre debe ser un número entero.',
            'parent_id.required' => 'La categoría padre es obligatoria.',
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
