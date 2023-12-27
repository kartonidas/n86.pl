<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\BalanceDocument;

class BalanceDocumentObserver
{
    public function created(BalanceDocument $balanceDocument): void
    {
        $balanceDocument->setBalance(); 
    }
    
    public function updated(BalanceDocument $balanceDocument): void
    {
        if($balanceDocument->object_type != BalanceDocument::OBJECT_TYPE_DEPOSIT)
            $balanceDocument->setObjectPaid();
            
        $balanceDocument->setBalance();
    }
    
    public function deleted(BalanceDocument $balanceDocument): void
    {
        $balanceDocument->setBalance();
    }
}
