<?php

namespace App\Libraries\Documents;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\Exception;
use App\Libraries\Documents\Object\ItemBill as ItemBillDocument;
use App\Models\ItemBill;

class ObjectToDocument
{
    public static function getObject(Model $object)
    {
        switch(get_class($object))
        {
            case ItemBill::class:
                return new ItemBillDocument($object);
            break;
        }
        
        throw new Exception(__("Invalid type"));
    }
}
