<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ItemCyclicalFeeCost extends Model
{
    protected $casts = [
        "cost" => "float",
    ];
    
    protected function fromTimeString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["from_time"] ? date("Y-m-d", $attributes["from_time"]) : null,
        );
    }
    
    public function prepareViewData()
    {
        $this->from_time = $this->fromTimeString;
    }
    
    public function canDelete()
    {
        $row = self::where("item_cyclical_fee_id", $this->item_cyclical_fee_id)->orderBy("from_time", "ASC")->first();
        if($row && $row->id == $this->id)
            return false;
        
        return true;
    }
    
    public function delete()
    {
        if(!$this->canDelete())
            throw new InvalidStatus(__("Cannot delete object"));
        
        return parent::delete();
    }
}