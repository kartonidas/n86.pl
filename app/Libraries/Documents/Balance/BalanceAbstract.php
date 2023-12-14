<?php

namespace App\Libraries\Documents\Balance;

use App\Libraries\Documents\Object\ObjectAbstract;
use App\Models\Balance;
use App\Models\BalanceDocument;

abstract class BalanceAbstract
{
    abstract public function __construct(ObjectAbstract $object, ?BalanceDocument $document);
    abstract public static function make(ObjectAbstract $object, ?BalanceDocument $document);
    abstract protected function getOperation();
    abstract protected function onCreate();
    abstract protected function onUpdate();
    abstract protected function onDelete();
    
    protected function createBalanceDocument()
    {
        $objectData = $this->object->getData();
        
        $balanceDocument = new BalanceDocument;
        $balanceDocument->time = time();
        $balanceDocument->object_type = $this->object->getType();
        $balanceDocument->object_id = $this->object->getId();
        $balanceDocument->amount = $objectData["amount"];
        $balanceDocument->operation_type = $this->getOperation();
        $balanceDocument->save();
        
        return $balanceDocument;
    }
    
    protected function createBalance(BalanceDocument $balanceDocument)
    {
        $objectData = $this->object->getData();
        
        $balance = new Balance;
        $balance->uuid = $objectData["uuid"];
        $balance->item_id = $objectData["item_id"];
        $balance->rental_id = $objectData["rental_id"];
        $balance->amount = ($balanceDocument->operation_type === "-" ? -1 : 1) * $balanceDocument->amount;
        $balance->balance_document_id = $balanceDocument->id;
        $balance->save();
    }
    
    protected function getBalance()
    {
        $objectData = $this->object->getData();
        return Balance::where("uuid", $objectData["uuid"])->where("balance_document_id", $this->document->id)->withoutGlobalScopes()->first();
    }
}
