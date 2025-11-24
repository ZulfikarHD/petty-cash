<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-transactions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'description' => ['required', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipts' => ['nullable', 'array'],
            'receipts.*' => ['image', 'max:5120'], // 5MB max per image
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'The transaction type is required.',
            'type.in' => 'The transaction type must be either cash in or cash out.',
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'amount.max' => 'The amount is too large.',
            'description.required' => 'The description field is required.',
            'description.max' => 'The description must not exceed 1000 characters.',
            'transaction_date.required' => 'The transaction date is required.',
            'transaction_date.date' => 'Please enter a valid date.',
            'transaction_date.before_or_equal' => 'The transaction date cannot be in the future.',
            'notes.max' => 'The notes must not exceed 2000 characters.',
            'receipts.*.image' => 'Each receipt must be an image file.',
            'receipts.*.max' => 'Each receipt must not exceed 5MB.',
        ];
    }
}
