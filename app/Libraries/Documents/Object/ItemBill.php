<?php

namespace App\Libraries\Documents\Object;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\Exception;
use App\Libraries\Documents\Object\ObjectAbstract;

class ItemBill extends ObjectAbstract
{
    private $object;
    public function __construct(Model $object)
    {
        $this->object = $object;
    }
    
    public function getId()
    {
        return $this->object->id;
    }
    
    public function getType()
    {
        return "bill";
    }
    
    public function getObject()
    {
        return $this->object;
    }
    
    public function getData()
    {
        if(!($item = $this->object->item()->withoutGlobalScopes()->first()))
            throw new Exception(__("Item does not exist"));
        
        $rental = $item->getCurrentRental();
        
        return [
            "uuid" => $this->object->uuid,
            "amount" => $this->object->cost,
            "item_id" => $this->object->item_id,
            "rental_id" => $rental ? $rental->id : 0,
        ];
    }
}