<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\ItemBill;

class ItemBillObserver
{
    public function created(ItemBill $bill): void
    {
    }
    
    public function updated(ItemBill $bill): void
    {
    }
    
    public function deleted(ItemBill $bill): void
    {
    }
}