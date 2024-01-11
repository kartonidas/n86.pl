<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\BalanceDocument;

class BalanceDocumentObserver
{
    public function creating(BalanceDocument $balanceDocument): void
    {
        if(Auth::check())
            $balanceDocument->user_id = Auth::user()->id;
    }
    
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
        if($balanceDocument->object_type == BalanceDocument::OBJECT_TYPE_DEPOSIT)
        {
            $associatedDocuments = $balanceDocument->getDepositAssociatedDocument();
            foreach($associatedDocuments as $associatedDocument)
            {
                $associatedDocument->paid = 0;
                $associatedDocument->save();
            }
        }
            
        $balanceDocument->setBalance();
    }
}
