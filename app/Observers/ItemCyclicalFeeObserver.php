<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\History;
use App\Models\ItemCyclicalFee;
use App\Models\ItemCyclicalFeeCost;

class ItemCyclicalFeeObserver
{
    public function created(ItemCyclicalFee $item): void
    {
        History::log("create", $item);
        
        $cost = new ItemCyclicalFeeCost;
        $cost->item_cyclical_fee_id = $item->id;
        $cost->from_time = time();
        $cost->cost = $item->cost;
        $cost->save();
    }
    
    public function updated(ItemCyclicalFee $item): void
    {
        History::log("update", $item);
    }
    
    public function deleted(ItemCyclicalFee $item): void
    {
        History::log("delete", $item);
    }
}