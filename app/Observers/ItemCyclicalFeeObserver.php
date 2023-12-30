<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\ItemCyclicalFee;
use App\Models\ItemCyclicalFeeCost;

class ItemCyclicalFeeObserver
{
    public function created(ItemCyclicalFee $item): void
    {
        $cost = new ItemCyclicalFeeCost;
        $cost->item_cyclical_fee_id = $item->id;
        $cost->from_time = time();
        $cost->cost = $item->cost;
        $cost->save();
    }
}