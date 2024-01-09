<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Numbering;

class UpdateConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "owner_type" => ["required", Rule::in(Customer::TYPE_PERSON, Customer::TYPE_FIRM)],
            "owner_name" => "required|max:100",
            "owner_street" => "sometimes|max:80",
            "owner_house_no" => "sometimes|max:20",
            "owner_apartment_no" => "sometimes|max:20",
            "owner_city" => "sometimes|max:120",
            "owner_zip" => "sometimes|max:10",
            "owner_country" => ["sometimes", Rule::in(Country::getAllowedCodes())],
            "rental_numbering_mask" => "required|string|max:100",
            "rental_numbering_continuation" => ["required", "string", Rule::in(array_keys(Numbering::getNumberingContinuation()))],
        ];
    
        if(($this->owner_type ?? "") == Customer::TYPE_PERSON)
        {
            $rules["owner_pesel"] = ["sometimes", "max:15", new \App\Rules\Pesel];
            $rules["owner_document_type"] = ["sometimes", Rule::in(array_keys(Customer::getDocumentTypes()))];
            $rules["owner_document_number"] = ["sometimes", "max:100"];
            $rules["owner_document_extra"] = ["sometimes", "max:250"];
        }
        elseif(($this->owner_type ?? "") == Customer::TYPE_FIRM)
        {
            $rules["owner_nip"] = ["sometimes", "max:20", new \App\Rules\Nip];
            $rules["owner_regon"] = ["sometimes", "max:15", new \App\Rules\Regon];
        }
        
        return $rules;
    }
}