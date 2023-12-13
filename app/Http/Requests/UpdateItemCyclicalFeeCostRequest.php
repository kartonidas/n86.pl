<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Traits\RequestUpdateRules;

class UpdateItemCyclicalFeeCostRequest extends StoreItemCyclicalFeeCostRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        return $this->addSometimesToRules(parent::rules());
    }
}
