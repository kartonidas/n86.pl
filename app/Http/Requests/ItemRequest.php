<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Item;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
            "sort" => "nullable",
            "order" => "nullable|integer",
            "search.customer_id" => "sometimes|integer",
            "search.name" => "nullable|string",
            "search.type" => ["nullable", Rule::in(array_keys(Item::getTypes()))],
            "search.address" => "nullable|string",
            "search.rented" => "nullable|boolean",
        ];
    }
}
