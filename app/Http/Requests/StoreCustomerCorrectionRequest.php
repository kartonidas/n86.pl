<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Http\Requests\StoreCustomerInvoicesRequest;
use App\Models\SaleRegister;

class StoreCustomerCorrectionRequest extends StoreCustomerInvoicesRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        
        unset($rules["type"]);
        unset($rules["customer_id"]);
        unset($rules["recipient_id"]);
        unset($rules["payer_id"]);
        unset($rules["payment_type_id"]);
        unset($rules["language"]);
        unset($rules["currency"]);
        
        $saleRegisteires = SaleRegister::where("type", SaleRegister::TYPE_CORRECTION)->pluck("id")->all();
        $rules["sale_register_id"] = ["required", Rule::in($saleRegisteires)];
        
        return $rules;
    }
}
