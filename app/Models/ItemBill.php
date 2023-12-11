<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use App\Exceptions\InvalidStatus;

class ItemBill extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    public function canDelete()
    {
        return true;
    }
    
    public function delete()
    {
        if(!$this->canDelete())
            throw new InvalidStatus(__("Cannot delete object"));
        
        return parent::delete();
    }
    
    public function scopeApiFields(Builder $query): void
    {
        $query->select(
            "id",
            "item_id",
            "bill_type_id",
            "payment_date",
            "paid",
            "paid_date",
            "cost",
            "recipient_name",
            "recipient_desciption",
            "recipient_bank_account",
            "comments",
            "created_at"
        );
    }
}