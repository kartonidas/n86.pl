<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreFaultRequest;
use App\Traits\RequestUpdateRules;

class UpdateFaultRequest extends StoreFaultRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        return $this->addSometimesToRules(parent::rules());
    }
}
