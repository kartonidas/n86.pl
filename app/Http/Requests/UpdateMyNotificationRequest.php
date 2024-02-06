<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreMyNotificationRequest;
use App\Traits\RequestUpdateRules;

class UpdateMyNotificationRequest extends StoreMyNotificationRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules["type"]);
        
        return $this->addSometimesToRules($rules);
    }
}
