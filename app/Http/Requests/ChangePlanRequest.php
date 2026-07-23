<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where('is_active', true)],
            'interval' => ['required', Rule::in(['monthly', 'yearly'])],
        ];
    }
}
