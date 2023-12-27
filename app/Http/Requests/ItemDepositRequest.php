<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BalanceDocument;
use App\Models\Item;

class ItemDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemIds = Item::pluck("id")->all();
        $rules = [
            "item_id"=> ["required", Rule::in($itemIds)],
            "deposit_current_tenant" => "nullable|boolean",
            "amount" => "required|numeric|min:1|max:999999",
            "documents" => "sometimes|array",
            "paid_date" => "required|date_format:Y-m-d",
            "payment_method" => ["required", Rule::in(array_keys(BalanceDocument::getAvailablePaymentMethods()))],
            "comments" => "nullable|string|max:5000",
        ];
        
        if(!empty($this->item_id) && in_array($this->item_id, $itemIds))
        {
            $balanceDocumentIds = [];
            $balanceDocuments = BalanceDocument::getUnpaid($this->item_id);
            $rules["documents"] = ["sometimes", "array", Rule::in($balanceDocuments->pluck('id')->all())];
        }
        
        return $rules;
    }
}
