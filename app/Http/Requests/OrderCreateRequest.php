<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "total" => "required|numeric|min:1|max:1000",
            "period" => ["required", Rule::in(array_keys(config("packages.allowed")))],
        ];
        
        return $rules;
    }
}
