<?php

namespace App\Http\Requests;

use App\Services\BudgetService;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-budgets');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'alert_threshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $validator->errors()->has('category_id') &&
                ! $validator->errors()->has('start_date') &&
                ! $validator->errors()->has('end_date')) {

                $budgetService = app(BudgetService::class);

                if ($budgetService->hasOverlappingBudget(
                    $this->category_id,
                    $this->start_date,
                    $this->end_date
                )) {
                    $validator->errors()->add(
                        'start_date',
                        'A budget for this category already exists for this date range.'
                    );
                }
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
            'category_id.required' => 'The category field is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'amount.required' => 'The budget amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'amount.max' => 'The amount is too large.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'Please enter a valid start date.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after' => 'The end date must be after the start date.',
            'alert_threshold.numeric' => 'The alert threshold must be a number.',
            'alert_threshold.min' => 'The alert threshold must be at least 0.',
            'alert_threshold.max' => 'The alert threshold cannot exceed 100.',
        ];
    }
}
