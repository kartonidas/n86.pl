<?php

namespace App\Libraries\Documents\Balance;

use Illuminate\Support\Facades\DB;
use App\Exceptions\Exception;
use App\Libraries\Documents\Balance\BalanceAbstract;
use App\Libraries\Documents\Object\ObjectAbstract;
use App\Models\BalanceDocument;

/*
 * TODO:
 * - usuwanie pod warunkiem że dokument nie jest już opłacony (paid!=1)
 * - blokowanie aktualizacji jeśli dokument jest już opłacony (paid!=1)
 * - usuwać można tylko po wcześniejszym usunięciu wpłaty (wpłata jaki i powiązane roczlienia powinny być w dodatkwoej tabeli)
 *      usuniecie powinno wywalić te powiązania i ustawić flagę paid=0
 * - jeśli mamy nadpłatę jakąś na saldzie próbujemy automatycznie rozliczyć należności (ale tylko jeśli dodatnie saldo jest wystarczajće)
 *      tutaj być może przy rejestrowaniu wplaty ustawić że np chce rozliczyć tylko czynsze???
 */

class Charge extends BalanceAbstract
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
        return "-";
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
        DB::transaction(function () {
            $objectData = $this->object->getData();
            $this->document->amount = $objectData["amount"];
            $this->document->save();
            
            $balance = $this->getBalance();
            if(!$balance)
                throw new Exception(__("Balance row does not exists"));
            
            $balance->amount = $objectData["amount"];
            $balance->save();
        });
    }
    
    protected function onDelete()
    {
        
    }
}
