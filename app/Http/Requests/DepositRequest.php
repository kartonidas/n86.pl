<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepositRequest extends ListRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            "search.item_name" => "nullable|string",
            "search.item_address" => "nullable|string",
            "search.payment_method" => "nullable|string",
            "search.paid_date_from" => "nullable|date_format:Y-m-d",
            "search.paid_date_to" => "nullable|date_format:Y-m-d",
        ]);
    }
}
