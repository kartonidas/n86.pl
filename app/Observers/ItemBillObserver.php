<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Libraries\Documents\Balance;
use App\Models\ItemBill;

class ItemBillObserver
{
    public function created(ItemBill $bill): void
    {
        // wygenerwaonie dokumentu kwotowego
        // i proba rozliczenia jesli jest nadpalta (wew bibliteki)
        
        $balance = new Balance($bill);
        $balance->charge();
    }
    
    public function updated(ItemBill $bill): void
    {
        // aktualizacja dokumentu kwotowego
        // tylko jesli nie oplacone
        
        $balance = new Balance($bill);
        $balance->charge();
    }
    
    public function deleted(ItemBill $bill): void
    {
        // usuniecie dokumentu kwotowego
        // tylko jesli nie oplacone
    }
}