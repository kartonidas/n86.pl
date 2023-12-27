<?php

namespace App\Libraries\Balance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Exceptions\ObjectNotExist;
use App\Libraries\Balance\Abstract\BalanceAbstract;
use App\Libraries\Balance\Object\ChargeObject;
use App\Libraries\Balance\Object\DepositObject;

class Charge extends BalanceAbstract
{
    public function __construct(ChargeObject $operationObject)
    {
        $this->operationObject = $operationObject;
    }
    
    public static function make(ChargeObject|DepositObject $object)
    {
        return new self($object);
    }
    
    public function create()
    {
        DB::transaction(function () {
            $document = self::createBalanceDocument($this->operationObject);
            if(!$document)
                throw new ObjectNotExist(__("Cannot create balance document"));
            
            $document->amount = $this->operationObject->getAmount();
            $document->save();
        });
    }
    
    public function update()
    {
        DB::transaction(function () {
            $document = self::getBalanceDocument($this->operationObject);
            if(!$document)
                throw new ObjectNotExist(__("Balance document does not exists"));
            
            $document->amount = $this->operationObject->getAmount();
            $document->save();
        });
    }
    
    public function delete()
    {
        
    }
}