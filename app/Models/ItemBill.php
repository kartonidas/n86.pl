<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use App\Exceptions\InvalidStatus;
use App\Models\Dictionary;

class ItemBill extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $casts = [
        "cost" => "float",
    ];
    
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
            "source_document_number",
            "source_document_date",
            "comments",
            "created_at"
        );
    }
    
    private static $cachedBillTypes = [];
    public function getBillType()
    {
        if(!isset(self::$cachedBillTypes[$this->bill_type_id]))
            self::$cachedBillTypes[$this->bill_type_id] = Dictionary::apiFields()->find($this->bill_type_id);
        
        return self::$cachedBillTypes[$this->bill_type_id];
    }
}