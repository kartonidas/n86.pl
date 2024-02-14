<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemBillRequest extends ListRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            "search.item_id" => "nullable|integer",
            "search.item_name" => "nullable|string",
            "search.item_address" => "nullable|string",
            "search.bill_type_id" => "nullable|integer",
            "search.paid" => "nullable|integer|in:0,1",
            "search.payment_date_from" => "nullable|date_format:Y-m-d",
            "search.payment_date_to" => "nullable|date_format:Y-m-d",
        ]);
    }
}
