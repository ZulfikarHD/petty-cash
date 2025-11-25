<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReconcileBalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-transactions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'actual_balance' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'discrepancy_notes' => ['nullable', 'required_if:has_discrepancy,true', 'string', 'max:1000'],
            'has_discrepancy' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if the cash balance is already reconciled
            $cashBalance = $this->route('cashBalance');

            if ($cashBalance && $cashBalance->isReconciled()) {
                $validator->errors()->add(
                    'actual_balance',
                    'This cash balance period has already been reconciled.'
                );
            }

            if ($cashBalance && $cashBalance->isClosed()) {
                $validator->errors()->add(
                    'actual_balance',
                    'This cash balance period is closed and cannot be reconciled.'
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'actual_balance.required' => 'The actual cash balance is required.',
            'actual_balance.numeric' => 'The actual balance must be a valid number.',
            'actual_balance.min' => 'The actual balance cannot be negative.',
            'actual_balance.max' => 'The actual balance is too large.',
            'discrepancy_notes.required_if' => 'Please provide notes explaining the discrepancy.',
            'discrepancy_notes.max' => 'The discrepancy notes cannot exceed 1000 characters.',
        ];
    }
}
