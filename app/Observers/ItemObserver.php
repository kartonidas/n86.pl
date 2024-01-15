<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;

use App\Jobs\RecalculateStats;
use App\Models\History;
use App\Models\Item;

class ItemObserver
{
    public function created(Item $item): void
    {
        History::log("create", $item);
        Item::recalculate($item);
        RecalculateStats::dispatch($item->uuid);
    }
    
    public function updated(Item $item): void
    {
        History::log("update", $item);
        Item::recalculate($item);
        RecalculateStats::dispatch($item->uuid);
    }
    
    public function deleted(Item $item): void
    {
        History::log("delete", $item);
        Item::recalculate($item);
        RecalculateStats::dispatch($item->uuid);
    }
}