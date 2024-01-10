<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Traits\RequestUpdateRules;
use App\Models\Rental;

class UpdateRentalRequest extends StoreRentalRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        $rules = $this->addSometimesToRules(parent::rules());
        
        unset($rules["payment"]);
        if(!empty($this->id))
        {
            $rental = Rental::find($this->id);
            if($rental)
            {
                if($rental->hasPaidDeposit())
                    unset($rules["deposit"]);
                    
                if($rental->hasGeneratedRent())
                {
                    unset($rules["first_payment_date"]);
                    unset($rules["first_month_different_amount_value"]);
                    unset($rules["last_month_different_amount_value"]);
                }
            }
        }
        
        return $rules;
    }
}
