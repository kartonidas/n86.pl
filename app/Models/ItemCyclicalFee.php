<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dictionary;
use App\Models\ItemCyclicalFeeCost;

class ItemCyclicalFee extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $casts = [
        "cost" => "float",
    ];
    
    protected function beginning(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
    protected function sourceDocumentDate(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn (float $value) => $this->getCurrentCost(),
        );
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
            "beginning",
            "payment_day",
            "repeat_months",
            "tenant_cost",
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
    
    public function getCurrentCost()
    {
        $cost = ItemCyclicalFeeCost::where("item_cyclical_fee_id", $this->id)->where("from_time", "<=", time())->orderBy("from_time", "DESC")->first();
        if($cost)
            return $cost->cost;
        
        return $this->cost;
    }
}