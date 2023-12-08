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
        $item = $rental->item()->first();
        if($item)
        {
            $item->rented = 1;
            $item->save();
        }
    }
}
