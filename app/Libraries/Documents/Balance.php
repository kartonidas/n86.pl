<?php

namespace App\Libraries\Documents;

use Illuminate\Database\Eloquent\Model;

use App\Libraries\Documents\ObjectToDocument;
use App\Libraries\Documents\Balance\Charge;
use App\Libraries\Documents\Balance\Deposit;

use App\Models\ItemBill;

class Balance
{
    private $document;
    private $object;
    
    public function __construct(Model $object)
    {
        $this->object = ObjectToDocument::getObject($object);
        $this->document = $this->object->getDocument();
    }
    
    public function getDocument(Model $object)
    {
        return $this->document;
    }
    
    public function getObject(Model $object)
    {
        return $this->object;
    }
    
    public function charge()
    {
        Charge::make($this->object, $this->document);
    }
    
    public function deposit()
    {
        Deposit::make($this->object, $this->document);
    }
}
