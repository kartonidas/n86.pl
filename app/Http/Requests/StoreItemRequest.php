<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\Rule;
use App\Models\Customer;
use App\Models\Country;
use App\Models\Item;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "id" => "sometimes|integer",
            "type" => ["required", Rule::in(array_keys(Item::getTypes()))],
            "name" => "required|max:100",
            "street" => "required|max:80",
            "house_no" => "sometimes|nullable|max:20",
            "apartment_no" => "sometimes|nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
            "country" => ["sometimes", "nullable", Rule::in(Country::getAllowedCodes())],
            "area" => "sometimes|nullable|numeric",
            "ownership_type" => ["required", Rule::in(array_keys(Item::getOwnershipTypes()))],
            "room_rental" => "sometimes|required|boolean",
            "num_rooms" => "sometimes|nullable|integer",
            "description" => "sometimes|max:5000",
            "default_rent" => "sometimes|nullable|numeric",
            "default_deposit" => "sometimes|nullable|numeric",
            "comments" => "sometimes|max:5000",
        ];
        
        if(!empty($this->id))
        {
            $rules["id"] = Rule::exists('items', 'id')->where(function(Builder $query) {
                return $query->where('uuid', Auth::user()->getUuid());
            });
        }
        
        if(($this->ownership_type ?? "") == Item::OWNERSHIP_MANAGE)
        {
            $customers = Customer::pluck("id");
            $rules["customer_id"] = ["required", Rule::in($customers->all())];
        }
        
        return $rules;
    }
    
    public function messages()
    {
        return [
            'id' => ['exists' => __('Selected item does not exists')]
        ];
    }
}
