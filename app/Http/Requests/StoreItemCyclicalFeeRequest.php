<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use App\Models\Dictionary;

class StoreItemCyclicalFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedDictionaryIds = Dictionary::where("type", "bills")->pluck("id")->all();
        
        return [
            "bill_type_id" => ["required", "integer", "gt:0", Rule::in($allowedDictionaryIds ?? [])],
            "payment_day" => "required|integer|min:1|max:25",
            "repeat_months" => "required|integer|min:1|max:3",
            "tenant_cost" => "sometimes|boolean",
            "cost" => "required|numeric|gt:0",
            "recipient_name" => "nullable|string|max:250",
            "recipient_desciption" => "nullable|string|max:5000",
            "recipient_bank_account" => "nullable|string|max:50",
            "comments" => "nullable|string|max:5000",
        ];
    }
}
