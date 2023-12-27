<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Traits\RequestUpdateRules;

class UpdateItemBillRequest extends StoreItemBillRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        $rules = $this->addSometimesToRules(parent::rules());
        unset($rules["charge_current_tenant"]);
        return $rules;
    }
}
