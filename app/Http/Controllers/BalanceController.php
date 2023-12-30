<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\Exception;
use App\Exceptions\ObjectNotExist;
use App\Http\Requests\ItemDepositRequest;
use App\Http\Requests\UpdateItemDepositRequest;
use App\Libraries\Balance\Balance;
use App\Libraries\Balance\Object\DepositObject;
use App\Libraries\Helper;
use App\Models\BalanceDocument;
use App\Models\Item;

class BalanceController extends Controller
{
    public function itemDeposit(ItemDepositRequest $request)
    {
        $validated = $request->validated();
        
        $documentIds = [];
        if(!empty($validated["documents"]))
        {
            $documentsAmount = 0;
            $documents = BalanceDocument::whereIn("id", $validated["documents"])->get();
            foreach($documents as $document)
            {
                $documentsAmount += abs($document->amount);
                $documentIds[] = $document->id;
            }
                
            if($validated["amount"] < $documentsAmount)
                throw new Exception(__("Value of the documents exceeds the amount paid"));
        }
        
        if(!empty($validated["deposit_current_tenant"]))
        {
            $item = Item::find($validated["item_id"]);
            if($item)
            {
                $rental = $item->getCurrentRental();
                if($rental)
                    $validated["rental_id"] = $rental->id;
            }
        }
        
        $deposit = DepositObject::makeFromParams($validated["item_id"], $validated["rental_id"] ?? 0, BalanceDocument::OBJECT_TYPE_DEPOSIT, 0, $validated["amount"]);
        
        $deposit->setDocumentIds($documentIds);
        $deposit->setPayment(Helper::setDateTime($validated["paid_date"], "00:00:00", true), $validated["payment_method"]);
        
        if(isset($validated["comments"]))
            $deposit->setComment($validated["comments"]);
        
        Balance::deposit($deposit)->create();
        
        return true;
    }
    
    public function updateItemDeposit(UpdateItemDepositRequest $request, $id)
    {
        $document = BalanceDocument::find($id);
        if(!$document)
            throw new ObjectNotExist(__("Balance document does not exist"));
        
        if($document->object_type != BalanceDocument::OBJECT_TYPE_DEPOSIT)
            throw new ObjectNotExist(__("Balance document does not exist"));
        
        $deposit = DepositObject::makeFromBalanceDocument($document);
        
        $validated = $request->validated();
        if(isset($validated["amount"]))
        {
            $associatedDocuments = $document->getDepositAssociatedDocument();
            if($associatedDocuments)
            {
                $documentsAmount = 0;
                foreach($associatedDocuments as $associatedDocument)
                    $documentsAmount += abs($associatedDocument->amount);
            }
            if($validated["amount"] < $documentsAmount)
                throw new Exception(__("Value of the documents exceeds the amount paid"));
            
            $deposit->setAmount($validated["amount"]);
        }
        
        $paidDate = !empty($validated["paid_date"]) ? Helper::setDateTime($validated["paid_date"], "00:00:00", true) : $document->paid_date;
        $paymentMethod = !empty($validated["payment_method"]) ? $validated["payment_method"] : $document->payment_method;
        
        $deposit->setPayment($paidDate, $paymentMethod);
        
        if(isset($validated["comments"]))
            $deposit->setComment($validated["comments"]);
        
        Balance::deposit($deposit)->update();
        
        return true;
    }
    
    public function deleteItemDeposit(Request $request, $id)
    {
        $document = BalanceDocument::find($id);
        if(!$document)
            throw new ObjectNotExist(__("Balance document does not exist"));
        
        if($document->object_type != BalanceDocument::OBJECT_TYPE_DEPOSIT)
            throw new ObjectNotExist(__("Balance document does not exist"));
        
        $deposit = DepositObject::makeFromBalanceDocument($document);
        Balance::deposit($deposit)->delete();
        
        return true;
    }
}
