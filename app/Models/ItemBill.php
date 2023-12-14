<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Exceptions\InvalidStatus;
use App\Models\Dictionary;
use App\Models\Item;

class ItemBill extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $casts = [
        "cost" => "float",
    ];
    protected $hidden = ["uuid"];
    
    protected function paymentDate(): Attribute
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
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}