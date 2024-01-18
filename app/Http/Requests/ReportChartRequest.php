<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "year" => "sometimes|integer|min:2020|max:2100",
            "period" => "sometimes|string|in:last_year",
        ];
    }
}
