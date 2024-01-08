<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\DocumentTemplate;

class RentalTemplateDocumentRequest extends ListRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            "type" => ["required", Rule::in(array_keys(DocumentTemplate::getTypes()))],
            "template" => "required|integer",
        ];
        
        return $rules;
    }
}

