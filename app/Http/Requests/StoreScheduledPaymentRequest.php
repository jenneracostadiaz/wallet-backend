<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreScheduledPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'payment_type' => ['required', Rule::enum(PaymentType::class)],
            'status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'amount' => 'required|numeric|min:0.01',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:10',
            'start_date' => 'required|date|after_or_equal:today',
            'next_payment_date' => 'nullable|date|after_or_equal:start_date',
            'end_date' => 'nullable|date|after:start_date',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'metadata' => 'nullable|array',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del pago programado es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'payment_type.required' => 'El tipo de pago es obligatorio.',
            'amount.required' => 'El monto es obligatorio.',
            'amount.min' => 'El monto debe ser mayor a 0.',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF0000).',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',
            'next_payment_date.after_or_equal' => 'La fecha del próximo pago debe ser posterior o igual a la fecha de inicio.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'account_id.required' => 'La cuenta es obligatoria.',
            'account_id.exists' => 'La cuenta seleccionada no existe.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }

    protected function prepareForValidation()
    {
        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge(['status' => PaymentStatus::Active->value]);
        }

        // Set next_payment_date to start_date if not provided
        if (!$this->has('next_payment_date') && $this->has('start_date')) {
            $this->merge(['next_payment_date' => $this->start_date]);
        }
    }
}
