<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Customer;

class TenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
            "sort" => "nullable",
            "order" => "nullable|integer",
            "search.name" => "nullable|string",
            "search.type" => ["nullable", Rule::in(Customer::TYPE_PERSON, Customer::TYPE_FIRM)],
            "search.pesel_nip" => "nullable|string",
            "search.address" => "nullable|string",
        ];
    }
}