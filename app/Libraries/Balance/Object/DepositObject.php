<?php

namespace App\Libraries\Balance\Object;

use Illuminate\Database\Eloquent\Model;

use App\Libraries\Balance\Object\ObjectAbstract;
use App\Exceptions\Exception;
use App\Models\BalanceDocument;
use App\Models\ItemBill;

class DepositObject extends ObjectAbstract
{
    public static function makeFromModel(Model $object)
    {
        throw new Exception(__("Method not available"));
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
        $object = new self;
        $object->id = $document->id;
        $object->itemId = $document->item_id;
        $object->rentalId = $document->rental_id;
        $object->objectType = $document->object_type;
        $object->objectId = $document->object_id;
        $object->amount = $document->amount;
        $object->comments = $document->comments;
        return $object;
    }
}