<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreTenantRequest;
use App\Traits\RequestUpdateRules;

class UpdateTenantRequest extends StoreTenantRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        return $this->addSometimesToRules(parent::rules());
    }
}
