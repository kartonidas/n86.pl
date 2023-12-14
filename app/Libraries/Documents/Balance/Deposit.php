<?php

namespace App\Libraries\Documents\Balance;

use Illuminate\Support\Facades\DB;
use App\Libraries\Documents\Balance\BalanceAbstract;
use App\Libraries\Documents\Object\ObjectAbstract;
use App\Models\BalanceDocument;

class Deposit extends BalanceAbstract
{
    protected $object;
    protected $document;
    public function __construct(ObjectAbstract $object, ?BalanceDocument $document)
    {
        $this->object = $object;
        $this->document = $document;
    }
    
    public static function make(ObjectAbstract $object, ?BalanceDocument $document)
    {
        print_r($object);
        print_r($document);
    }
    
    protected function getOperation()
    {
        return "+";
    }
    
    protected function onCreate()
    {
        
    }
    
    protected function onUpdate()
    {
        
    }
    
    protected function onDelete()
    {
        
    }
}
