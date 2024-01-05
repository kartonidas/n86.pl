<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Libraries\Helper;
use App\Models\Item;
use App\Models\Rental;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "start_date" => "required|date_format:Y-m-d",
            "document_date" => "required|date_format:Y-m-d",
            "period" => ["required", Rule::in(array_keys(Rental::getPeriods()))],
        ];
        
        if(!empty($this->item_id))
        {
            $items = Item::pluck("id");
            $rules["item_id"] = ["required", Rule::in($items->all())];
        }
        
        if(($this->period ?? null) == Rental::PERIOD_DATE)
        {
            $rules["end_date"] = "required|date_format:Y-m-d";
            if(!empty($this->start_date) && Helper::_validateDate($this->start_date))
                $rules["end_date"] .= "|after_or_equal:" . $this->start_date;
        }
            
        if(($this->period ?? null) == Rental::PERIOD_MONTH)
            $rules["months"] = "required|integer|min:1|max:120";
        
        $rules["termination_period"] = ["required", Rule::in(array_keys(Rental::getTerminationPeriods()))];
        
        if(($this->termination_period ?? null) == Rental::PERIOD_TERM_MONTHS)
            $rules["termination_months"] = "required|integer|min:1|max:24";
            
        if(($this->termination_period ?? null) == Rental::PERIOD_TERM_DAYS)
            $rules["termination_days"] = "required|integer|min:1|max:99";
        
        $rules["deposit"] = "nullable|numeric|max:999999.99";
        $rules["payment"] = ["required", Rule::in(array_keys(Rental::getPaymentsType()))];
        $rules["rent"] = "required|numeric|min:1|max:999999.99";
        
        if(($this->payment ?? null) == Rental::PAYMENT_CYCLICAL)
        {
            $rules["first_month_different_amount_value"] = "nullable|numeric|max:999999.99";
            $rules["last_month_different_amount_value"] = "nullable|numeric|max:999999.99";
            $rules["payment_day"] = ["required", Rule::in(array_keys(Rental::getPaymentDays()))];
        }
            
        $rules["first_payment_date"] = "required|date_format:Y-m-d|after_or_equal:" . date("Y-m-d");
        
        $rules["number_of_people"] = "required|integer|min:1|max:99";
        $rules["comments"] = "nullable|max:5000";
        
        return $rules;
    }
}
