<?php

namespace App\Libraries\Balance;

use Illuminate\Database\Eloquent\Model;

use App\Libraries\Balance\Charge;
use App\Libraries\Balance\Deposit;
use App\Libraries\Balance\Object\ChargeObject;
use App\Libraries\Balance\Object\DepositObject;

class Balance {
    public static function charge(ChargeObject $object)
    {
        return Charge::make($object);
    }
    
    public static function deposit(DepositObject $object)
    {
        return Deposit::make($object);
    }
}
