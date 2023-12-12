<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Dictionary;

class StoreItemBillRequest extends FormRequest
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
            "payment_date" => "required|date_format:Y-m-d",
            "paid" => "nullable|boolean",
            "paid_date" => "nullable|date_format:Y-m-d",
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
