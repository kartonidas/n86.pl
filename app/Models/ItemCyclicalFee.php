<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helper;
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
    protected $hidden = ["uuid"];
    
    public static function getPaymentDays()
    {
        $days = [];
        for($i = 1; $i <= 25; $i++)
            $days[$i] = $i;
        return $days;
    }
    
    public static function getRepeatMonths()
    {
        $days = [];
        for($i = 1; $i <= 3; $i++)
            $days[$i] = $i . " " . Helper::plurals($i, __("month"), __("months"), __("months"));
        return $days;
    }
    
    
    protected function sourceDocumentDateString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["source_document_date"] ? date("Y-m-d", $attributes["source_document_date"]) : null,
        );
    }
    
    public function prepareViewData()
    {
        $this->source_document_date = $this->sourceDocumentDateString;
    }
    
    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn (float $value) => $this->getCurrentCost($value),
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
    
    private static $cachedBillTypes = [];
    public function getBillType()
    {
        if(!isset(self::$cachedBillTypes[$this->bill_type_id]))
            self::$cachedBillTypes[$this->bill_type_id] = Dictionary::find($this->bill_type_id);
        
        return self::$cachedBillTypes[$this->bill_type_id];
    }
    
    public function getCurrentCost($defaultCost)
    {
        $cost = ItemCyclicalFeeCost::where("item_cyclical_fee_id", $this->id)->where("from_time", "<=", time())->orderBy("from_time", "DESC")->first();
        if($cost)
            return $cost->cost;
        
        return $defaultCost;
    }
}