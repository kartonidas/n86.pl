<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "period" => ["sometimes"]
        ];
        if(isset($this->period) && is_numeric($this->period))
        {
            $rules["period"][] = "integer";
            $rules["period"][] = "min:2020";
            $rules["period"][] = "max:2100";
        }
        else
        {
            $rules["period"][] = "string";
            $rules["period"][] = "in:last_year";
        }
        
        return $rules;
    }
}
