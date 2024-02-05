<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Item;

class ItemRequest extends ListRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            "search.customer_id" => "sometimes|integer",
            "search.name" => "nullable|string",
            "search.type" => ["nullable", Rule::in(array_keys(Item::getTypes()))],
            "search.address" => "nullable|string",
            "search.rented" => "nullable|boolean",
            "search.mode" => ["nullable", Rule::in([Item::MODE_NORMAL, Item::MODE_ARCHIVED, Item::MODE_LOCKED, "all"])],
        ]);
    }
}
