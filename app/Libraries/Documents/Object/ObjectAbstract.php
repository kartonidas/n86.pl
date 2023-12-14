<?php

namespace App\Libraries\Documents\Object;

use Illuminate\Database\Eloquent\Model;
use App\Models\BalanceDocument;

abstract class ObjectAbstract
{
    abstract public function __construct(Model $object);
    abstract public function getId();
    abstract public function getType();
    abstract public function getData();
    abstract public function getObject();
    
    public function getDocument() : ?BalanceDocument
    {
        $object = $this->getObject();
        $balanceDocument = BalanceDocument::where("object_type", $this->getType())->where("object_id", $object->id)->first();
        
        return $balanceDocument;
    }
}