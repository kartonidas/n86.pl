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
        $balance = new self($object, $document);
        
        if(!$document)
            $balance->onCreate();
        else
            $balance->onUpdate();
    }
    
    protected function getOperation()
    {
        return "+";
    }
    
    protected function onCreate()
    {
        DB::transaction(function () {
            $balanceDocument = $this->createBalanceDocument();
            $this->createBalance($balanceDocument);
        });
    }
    
    protected function onUpdate()
    {
        
    }
    
    protected function onDelete()
    {
        
    }
}
