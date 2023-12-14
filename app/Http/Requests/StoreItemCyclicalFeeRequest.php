<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use App\Models\Dictionary;
use App\Models\Item;

class StoreItemCyclicalFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $items = Item::pluck("id");
        $allowedDictionaryIds = Dictionary::where("type", "bills")->pluck("id")->all();
        
        return [
            "item_id" => ["required", Rule::in($items->all())],
            "bill_type_id" => ["required", "integer", "gt:0", Rule::in($allowedDictionaryIds ?? [])],
            "beginning" => "required|date_format:Y-m-d",
            "payment_day" => "required|integer|min:1|max:25",
            "repeat_months" => "required|integer|min:1|max:3",
            "tenant_cost" => "required|boolean",
            "cost" => "required|numeric|gt:0",
            "recipient_name" => "nullable|string|max:250",
            "recipient_desciption" => "nullable|string|max:5000",
            "recipient_bank_account" => "nullable|string|max:50",
            "source_document_number" => "nullable|string|max:100",
            "source_document_date" => "nullable|date_format:Y-m-d",
            "comments" => "nullable|string|max:5000",
        ];
    }
}
