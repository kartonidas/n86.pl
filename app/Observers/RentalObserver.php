<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\History;
use App\Models\Rental;

class RentalObserver
{
    public function creating(Rental $rental): void
    {
        History::log("create", $rental);
        $rental->status = $rental->initStatus();
    }
    
    public function created(Rental $rental): void
    {
        $rental->setNumber();
        
        $item = $rental->item()->first();
        if($item)
            $item->setRentedFlag();
            
        Rental::recalculate($rental);
    }
    
    public function updated(Rental $rental): void
    {
        History::log("update", $rental);
        Rental::recalculate($rental);
    }
    
    public function deleted(Rental $rental): void
    {
        History::log("delete", $rental);
        Rental::recalculate($rental);
    }
}
