<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Traits\RequestUpdateRules;

class UpdateItemCyclicalFeeRequest extends StoreItemCyclicalFeeRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        $rules = $this->addSometimesToRules(parent::rules());
        
        unset($rules["cost"]);
        unset($rules["repeat_months"]);
        
        return $rules;
    }
}
