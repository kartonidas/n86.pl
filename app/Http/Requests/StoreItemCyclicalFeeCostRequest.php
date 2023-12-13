<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemCyclicalFeeCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "id" => "sometimes|integer",
            "from_time" => "required|date_format:Y-m-d",
            "cost" => "required|numeric|gt:0",
        ];
    }
}
