<?php

namespace App\Libraries\Balance\Abstract;

use App\Libraries\Balance\Object\ChargeObject;
use App\Libraries\Balance\Object\DepositObject;
use App\Models\Balance;
use App\Models\BalanceDocument;

abstract class BalanceAbstract {
    protected $operationObject;
    
    abstract public function create();
    abstract public function update();
    abstract public function delete();
    abstract public static function make(ChargeObject|DepositObject $object);
    
    protected static function getBalanceDocument(ChargeObject|DepositObject $object) : BalanceDocument|null
    {
        if($object->getId() > 0)
            return BalanceDocument::find($object->getId());
        
        return null;
    }
    
    protected static function createBalanceDocument(ChargeObject|DepositObject $object) : BalanceDocument
    {
        $balance = Balance::ensureBalance($object->getItemId(), $object->getRentalId());
        
        $document = new BalanceDocument;
        $document->time = time();
        $document->item_id = $object->getItemId();
        $document->object_type = $object->getObjectType();
        $document->object_id = $object->getObjectId();
        $document->amount = 0;
        $document->balance_id = $balance->id;
        
        return $document;
    }
}
