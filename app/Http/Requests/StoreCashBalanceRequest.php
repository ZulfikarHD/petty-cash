<?php

namespace App\Http\Requests;

use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreCashBalanceRequest extends FormRequest
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
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after:period_start'],
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $validator->errors()->has('period_start') &&
                ! $validator->errors()->has('period_end')) {

                $balanceService = app(BalanceService::class);

                if ($balanceService->hasOverlappingPeriod(
                    Carbon::parse($this->period_start),
                    Carbon::parse($this->period_end)
                )) {
                    $validator->errors()->add(
                        'period_start',
                        'A cash balance record already exists for this date range.'
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
            'period_start.required' => 'The period start date is required.',
            'period_start.date' => 'Please enter a valid start date.',
            'period_end.required' => 'The period end date is required.',
            'period_end.date' => 'Please enter a valid end date.',
            'period_end.after' => 'The end date must be after the start date.',
            'opening_balance.required' => 'The opening balance is required.',
            'opening_balance.numeric' => 'The opening balance must be a valid number.',
            'opening_balance.min' => 'The opening balance cannot be negative.',
            'opening_balance.max' => 'The opening balance is too large.',
            'notes.max' => 'The notes cannot exceed 1000 characters.',
        ];
    }
}
