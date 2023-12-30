<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Traits\RequestUpdateRules;
use App\Models\ItemBill;

class UpdateItemBillRequest extends StoreItemBillRequest
{
    use RequestUpdateRules;
    
    public function rules(): array
    {
        $rules = $this->addSometimesToRules(parent::rules());
        unset($rules["charge_current_tenant"]);
        
        if(!empty($this->id))
        {
            $itemBill = ItemBill::find($this->id);
            if($itemBill && $itemBill->bill_type_id < 0)
                unset($rules["bill_type_id"]);
        }
        
        return $rules;
    }
}
