<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\Rental;

class RentalObserver
{
    public function creating(Rental $rental): void
    {
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
        Rental::recalculate($rental);
    }
    
    public function deleted(Rental $rental): void
    {
        Rental::recalculate($rental);
    }
}
