<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            "period" => ["required", Rule::in(array_keys(Rental::getPeriods()))],
        ];
        
        if(($this->period ?? null) == Rental::PERIOD_DATE)
            $rules["end_date"] = "required|date_format:Y-m-d";
            
        if(($this->period ?? null) == Rental::PERIOD_MONTH)
            $rules["months"] = "required|integer|min:1";
        
        $rules["termination_period"] = ["required", Rule::in(array_keys(Rental::getTerminationPeriods()))];
        
        if(($this->termination_period ?? null) == Rental::PERIOD_TERM_MONTHS)
            $rules["termination_months"] = "required|min:1|max:999";
            
        if(($this->termination_period ?? null) == Rental::PERIOD_TERM_DAYS)
            $rules["termination_days"] = "required|min:1|max:99";
        
        $rules["payment"] = ["required", Rule::in(array_keys(Rental::getPaymentsType()))];
        $rules["rent"] = "required|numeric|min:1";
        
        if(($this->payment ?? null) == Rental::PAYMENT_CYCLICAL)
        {
            $rules["first_payment_date"] = "required|date_format:Y-m-d";
            $rules["payment_day"] = ["required", Rule::in(array_keys(Rental::getPaymentDays()))];
        }
                    
        return $rules;
    }
}
