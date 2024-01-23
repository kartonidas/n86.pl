<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Models\Customer;
use App\Models\Dictionary;
use App\Models\SaleRegister;
use App\Models\User;

class StoreCustomerInvoicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rule = [];
        $customerIds = Customer::pluck("id")->all();
        $userIds = User::pluck("id")->all();
        $paymentTypeIds = Dictionary::where("type", "payment_types")->pluck("id")->all();
        
        $rule["type"] = ["required", Rule::in(array_keys(SaleRegister::getAllowedTypes(true)))];
        if(!empty($this->type) && in_array($this->type, array_keys(SaleRegister::getAllowedTypes(true))))
        {
            $saleRegisteires = SaleRegister::where("type", $this->type)->pluck("id")->all();
            $rule["sale_register_id"] = ["required", Rule::in($saleRegisteires)];
        }
        $rule["created_user_id"] = ["required", Rule::in($userIds)];
        $rule["customer_id"] = ["required", Rule::in($customerIds)];
        $rule["recipient_id"] = ["nullable", Rule::in($customerIds)];
        $rule["payer_id"] = ["nullable", Rule::in($customerIds)];
        $rule["comment"] = ["nullable", "max:5000"];
        $rule["document_date"] = ["required", "date_format:Y-m-d"];
        $rule["sell_date"] = ["required", "date_format:Y-m-d"];
        $rule["payment_date"] = ["required", "date_format:Y-m-d"];
        $rule["payment_type_id"] = ["required", Rule::in($paymentTypeIds)];
        $rule["account_number"] = "sometimes|string|max:60";
        $rule["swift_number"] = "sometimes|string|max:60";
        $rule["language"] = ["required", Rule::in(config("invoice.languages"))];
        $rule["currency"] = ["required", Rule::in(config("invoice.currencies"))];
        
        $rule["items"] = "required|array";
        $rule["items.*.gtu"] = "sometimes|max:10";
        $rule["items.*.name"] = "required|max:200";
        $rule["items.*.quantity"] = "required|numeric|gt:0";
        $rule["items.*.unit_type"] = "required|string|max:50";
        $rule["items.*.net_amount"] = "required|numeric|gt:0";
        $rule["items.*.vat_value"] = ["required", Rule::in(array_keys(config("invoice.vat")))];
        $rule["items.*.discount"] = "sometimes|numeric|gt:0";
        return $rule;
    }
}
