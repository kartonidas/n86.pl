<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Dictionary;
use App\Models\Fault;

class StoreFaultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedDictionaryIds = Dictionary::where("type", "fault_statuses")->pluck("id")->all();
        
        return [
            "status_id" => ["required", "integer", "gt:0", Rule::in($allowedDictionaryIds ?? [])],
            "item_id" => "required|integer",
            "priority" => ["required", Rule::in(array_keys(Fault::getPriorities()))],
            "description" => "required|max:5000",
        ];
    }
}
