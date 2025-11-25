<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        // User must have edit permission and transaction must be pending
        // Users can only edit their own transactions unless they're admin
        return $this->user()->can('edit-transactions')
            && $transaction->isPending()
            && ($transaction->user_id === $this->user()->id || $this->user()->hasRole('Admin'));
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
            'category_id' => ['nullable', 'exists:categories,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipts' => ['nullable', 'array'],
            'receipts.*' => ['image', 'max:5120'], // 5MB max per image
            'delete_receipts' => ['nullable', 'array'],
            'delete_receipts.*' => ['integer', 'exists:media,id'],
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
            'category_id.exists' => 'The selected category does not exist.',
            'notes.max' => 'The notes must not exceed 2000 characters.',
            'receipts.*.image' => 'Each receipt must be an image file.',
            'receipts.*.max' => 'Each receipt must not exceed 5MB.',
            'delete_receipts.*.exists' => 'Invalid receipt selected for deletion.',
        ];
    }
}
