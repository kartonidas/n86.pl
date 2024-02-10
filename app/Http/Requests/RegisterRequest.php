<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            "email" => "required|email",
            "source" => ["nullable", Rule::in("app", "www")],
            "accept_regulation" => "required|boolean",
            "accept_privacy_policy" => "required|boolean",
        ];
        
        return $rules;
    }
}
