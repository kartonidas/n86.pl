<?php

namespace App\Libraries\Balance\Object;

use Illuminate\Database\Eloquent\Model;

use App\Exceptions\Exception;
use App\Models\BalanceDocument;
use App\Models\ItemBill;

class ChargeObject extends ObjectAbstract
{
    public static function makeFromModel(Model $object)
    {
        $self = new self();
        $self->setObjectData($object);
        return $self;
    }
    
    public static function makeFromParams(int $itemId, int $rentalId, string $objectType, int $objectId, float $amount)
    {
        $object = new self;
        $object->itemId = $itemId;
        $object->rentalId = $rentalId;
        $object->objectType = $objectType;
        $object->objectId = $objectId;
        $object->amount = $amount;
        return $object;
    }
    
    public static function makeFromBalanceDocument(BalanceDocument $document)
    {
    }
    
    private function setObjectData(Model $object)
    {
        switch(get_class($object))
        {
            case ItemBill::class:
                $balanceDocument = $object->getBalanceDocument();
                if($balanceDocument)
                    $this->id = $balanceDocument->id;
                
                $this->itemId = $object->item_id;
                $this->rentalId = $object->rental_id;
                $this->objectType = BalanceDocument::OBJECT_TYPE_BILL;
                $this->objectId = $object->id;
                $this->amount = -$object->cost;
            break;
        
            default:
                throw new Exception(__("Invalid type"));
        }
    }
}