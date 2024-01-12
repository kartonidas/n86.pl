<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Libraries\Balance\Balance;
use App\Libraries\Balance\Object\ChargeObject;
use App\Models\History;
use App\Models\ItemBill;

class ItemBillObserver
{
    public function created(ItemBill $bill): void
    {
        History::log("create", $bill);
        Balance::charge(ChargeObject::makeFromModel($bill))->create();
    }
    
    public function updated(ItemBill $bill): void
    {
        History::log("update", $bill);
        Balance::charge(ChargeObject::makeFromModel($bill))->update();
    }
    
    public function deleted(ItemBill $bill): void
    {
        History::log("delete", $bill);
        Balance::charge(ChargeObject::makeFromModel($bill))->delete();
    }
}