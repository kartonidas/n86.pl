<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;

class RentalPaymentRequest extends ListRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
