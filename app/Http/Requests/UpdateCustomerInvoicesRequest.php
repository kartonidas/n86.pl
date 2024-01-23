<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreCustomerInvoicesRequest;
use App\Traits\RequestUpdateRules;

class UpdateCustomerInvoicesRequest extends StoreCustomerInvoicesRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        $rules = $this->addSometimesToRules(parent::rules());
        unset($rules["type"]);
        return $rules;
    }
}
