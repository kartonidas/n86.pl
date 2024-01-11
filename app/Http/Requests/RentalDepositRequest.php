<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BalanceDocument;
use App\Models\Rental;

class RentalDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "cost" => "required|numeric|min:1|max:999999",
            "documents" => "sometimes|array",
            "paid_date" => "required|date_format:Y-m-d",
            "payment_method" => ["required", Rule::in(array_keys(BalanceDocument::getAvailablePaymentMethods()))],
            "comments" => "nullable|string|max:5000",
        ];
        
        if(!empty($this->id))
        {
            $rental = Rental::find($this->id);
            if($rental)
            {
                $unpaidBalanceDocuments = $rental->getUnpaidBalanceDocuments();
                if($unpaidBalanceDocuments)
                {
                    $unpaidBalanceDocumentIds = [];
                    foreach($unpaidBalanceDocuments as $unpaidBalanceDocument)
                        $unpaidBalanceDocumentIds[] = $unpaidBalanceDocument->id;
                    $rules["documents"] = ["sometimes", "array", Rule::in($unpaidBalanceDocumentIds)];
                }
            }
        }
        return $rules;
    }
}
