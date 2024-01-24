<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Country;

class UpdateCustomerInvoiceDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "type" => "required|in:firm,person",
            "street" => "required|max:80",
            "house_no" => "required|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
            "country" => ["required", Rule::in(Country::getAllowedCodes())],
        ];
        
        if(empty($this->type) || $this->type == "firm")
        {
            if(empty($this->country) || strtolower($this->country) == "pl")
                $rules["nip"] = ["required", new \App\Rules\Nip];
            else
                $rules["nip"] = "required";
                
            $rules["name"] = "required|max:200";
        }
        else
        {
            $rules["firstname"] = "required|max:100";
            $rules["lastname"] = "required|max:100";
        }
        
        return $rules;
    }
}

