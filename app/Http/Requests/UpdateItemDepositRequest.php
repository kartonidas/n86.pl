<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BalanceDocument;

class UpdateItemDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "amount" => "sometimes|numeric|min:1|max:999999",
            "paid_date" => "sometimes|date_format:Y-m-d",
            "payment_method" => ["sometimes", Rule::in(array_keys(BalanceDocument::getAvailablePaymentMethods()))],
            "comments" => "sometimes|string|max:5000",
        ];
        
        return $rules;
    }
}
