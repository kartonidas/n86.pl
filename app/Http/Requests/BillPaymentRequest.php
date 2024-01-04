<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BalanceDocument;

class BillPaymentRequest extends ListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "type" => ["required", Rule::in(["deposit", "setpaid"])],
        ];
        
        if(!empty($this->type) && $this->type == "deposit")
        {
            $rules["cost"] = "required|numeric|min:1|max:999999";
            $rules["paid_date"] = "required|date_format:Y-m-d";
            $rules["payment_method"] = ["required", Rule::in(array_keys(BalanceDocument::getAvailablePaymentMethods()))];
        }
        
        return $rules;
    }
}
