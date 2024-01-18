<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class TerminationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "end_date" => "required|date_format:Y-m-d",
            "termination_reason" => "sometimes|max:5000",
            "mode" => "required|in:date,immediately",
        ];
        
        return $rules;
    }
}
