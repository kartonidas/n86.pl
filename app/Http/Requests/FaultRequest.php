<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaultRequest extends ListRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            "search.status" => "sometimes|integer",
            "search.item_name" => "nullable|string",
            "search.item_address" => "nullable|string",
            "search.start" => ["nullable|date_format:Y-m-d"],
            "search.end" => "nullable|date_format:Y-m-d",
        ]);
    }
}
