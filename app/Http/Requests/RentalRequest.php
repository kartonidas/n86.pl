<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;

class RentalRequest extends ListRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            "search.item_id" => "nullable|integer",
            "search.tenant_id" => "nullable|integer",
            "search.status" => "nullable|string",
            "search.item_name" => "nullable|string",
            "search.item_address" => "nullable|string",
            "search.item_type" => ["nullable", "string", Rule::in(array_keys(Item::getTypes()))],
            "search.tenant_name" => "nullable|string",
            "search.tenant_address" => "nullable|string",
            "search.tenant_type" => ["nullable", "string", Rule::in([Customer::TYPE_FIRM, Customer::TYPE_PERSON])],
            "search.status" => ["nullable", "string", Rule::in(array_keys(Rental::getStatuses()))],
            "search.start" => "nullable|date_format:Y-m-d",
            "search.end" => "nullable|date_format:Y-m-d",
            "search.number" => "nullable|string",
        ]);
    }
}
