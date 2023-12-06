<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "item" => "required|array",
            "tenant" => "required|array",
            "rent" => "required|array",
        ];
    }
}
