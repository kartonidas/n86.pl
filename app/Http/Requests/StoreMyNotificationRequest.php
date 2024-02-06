<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Models\ConfigNotification;

class StoreMyNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "type" => ["required", Rule::in(array_keys(ConfigNotification::getAllowedTypes()))],
            "mode" => ["required"],
        ];
        
        if(!empty($this->type) && !empty(ConfigNotification::getAllowedTypes()[$this->type]))
        {
            $allowedTypes = ConfigNotification::getAllowedTypes()[$this->type];
            if(!empty($allowedTypes["days"]))
                $rules["days"] = "required|integer|in:" . implode(",", $allowedTypes["allowed_days"]);
                
            $rules["mode"][] = "in:" . implode(",", $allowedTypes["modes"]);
        }
        
        return $rules;
    }
}
