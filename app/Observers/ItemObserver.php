<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class ItemObserver
{
    public function created(Item $item): void
    {
        Item::recalculate($item);
    }
    
    public function updated(Item $item): void
    {
        Item::recalculate($item);
    }
    
    public function deleted(Item $item): void
    {
        Item::recalculate($item);
    }
}