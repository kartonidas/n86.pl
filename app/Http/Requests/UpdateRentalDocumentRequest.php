<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreRentalDocumentRequest;
use App\Traits\RequestUpdateRules;

class UpdateRentalDocumentRequest extends StoreRentalDocumentRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        return $this->addSometimesToRules(parent::rules());
    }
}
