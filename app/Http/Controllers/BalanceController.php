<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\Exception;
use App\Exceptions\ObjectNotExist;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\ItemBillRequest;
use App\Http\Requests\ItemDepositRequest;
use App\Http\Requests\UpdateItemDepositRequest;
use App\Libraries\Balance\Balance;
use App\Libraries\Balance\Object\DepositObject;
use App\Libraries\Helper;
use App\Models\BalanceDocument;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\User;
use App\Traits\Sortable;

class BalanceController extends Controller
{
    use Sortable;
    
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
    
    public function bills(ItemBillRequest $request)
    {
        User::checkAccess("item:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $bills = ItemBill::whereRaw("1=1");
            
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["item_name"]) || !empty($validated["search"]["item_address"]))
            {
                $items = Item::select("id");
                
                if(!empty($validated["search"]["item_name"]))
                    $items->where("name", "LIKE", "%" . $validated["search"]["item_name"] . "%");
                
                if(!empty($validated["search"]["item_address"]))
                {
                    $searchItemAddress = array_filter(explode(" ", $validated["search"]["item_address"]));
                    $items->where(function($q) use($searchItemAddress) {
                        $q
                            ->where("street", "REGEXP", implode("|", $searchItemAddress))
                            ->orWhere("city", "REGEXP", implode("|", $searchItemAddress));
                    });
                }
                
                $itemIds = $items->pluck("id")->all();
                $bills->whereIn("item_id", !empty($itemIds) ? $itemIds : [-1]);
            }
            
            if(!empty($validated["search"]["item_id"]))
                $bills->where("item_id", $validated["search"]["item_id"]);
                
            if(!empty($validated["search"]["bill_type_id"]))
                $bills->where("bill_type_id", $validated["search"]["bill_type_id"]);
                
            if(!empty($validated["search"]["payment_date_from"]))
            {
                $paymentDateFrom = Helper::setDateTime($validated["search"]["payment_date_from"], "00:00:00", true);
                $bills->where("payment_date", ">=", $paymentDateFrom);
            }
            
            if(!empty($validated["search"]["payment_date_to"]))
            {
                $paymentDateTo = Helper::setDateTime($validated["search"]["payment_date_to"], "23:59:59", true);
                $bills->where("payment_date", "<=", $paymentDateFrom);
            }
            
            if(isset($validated["search"]["paid"]))
                $bills->where("paid", $validated["search"]["paid"]);
        }
            
        $total = $bills->count();
        
        $orderBy = $this->getOrderBy($request, ItemBill::class, "payment_date,asc");
        $bills = $bills->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        foreach($bills as $i => $bill)
        {
            $bills[$i]->bill_type = $bill->getBillType();
            $bills[$i]->out_off_date = $bill->isOutOfDate();
            $bills[$i]->rental = $bill->getRental();
            $bills[$i]->item = $bill->getItem();
            $bills[$i]->prepareViewData();
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $bills,
        ];
            
        return $out;
    }
    
    public function deposits(DepositRequest $request)
    {
        User::checkAccess("item:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $itemIds = Item::pluck("id")->all();
        
        $deposits = BalanceDocument
            ::whereIn("item_id", $itemIds)
            ->where("object_type", BalanceDocument::OBJECT_TYPE_DEPOSIT);
           
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["item_name"]) || !empty($validated["search"]["item_address"]))
            {
                $items = Item::select("id");
                
                if(!empty($validated["search"]["item_name"]))
                    $items->where("name", "LIKE", "%" . $validated["search"]["item_name"] . "%");
                
                if(!empty($validated["search"]["item_address"]))
                {
                    $searchItemAddress = array_filter(explode(" ", $validated["search"]["item_address"]));
                    $items->where(function($q) use($searchItemAddress) {
                        $q
                            ->where("street", "REGEXP", implode("|", $searchItemAddress))
                            ->orWhere("city", "REGEXP", implode("|", $searchItemAddress));
                    });
                }
                
                $itemIds = $items->pluck("id")->all();
                $deposits->whereIn("item_id", !empty($itemIds) ? $itemIds : [-1]);
            }
            
            if(!empty($validated["search"]["payment_method"]))
                $deposits->where("payment_method", $validated["search"]["payment_method"]);
            
            if(!empty($validated["search"]["paid_date_from"]))
            {
                $paidDateFrom = Helper::setDateTime($validated["search"]["paid_date_from"], "00:00:00", true);
                $deposits->where("paid_date", ">=", $paidDateFrom);
            }
            
            if(!empty($validated["search"]["paid_date_to"]))
            {
                $paidDateTo = Helper::setDateTime($validated["search"]["paid_date_to"], "23:59:59", true);
                $deposits->where("paid_date", "<=", $paidDateTo);
            }
        }
            
        $total = $deposits->count();
        
        $orderBy = $this->getOrderBy($request, ItemBill::class, "paid_date,desc");
        $deposits = $deposits->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        foreach($deposits as $i => $deposit)
        {
            $deposits[$i]->item = $deposit->getItem();
            $deposits[$i]->associated_documents = $deposit->getAssociatedBills();
            $deposits[$i]->prepareViewData();
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $deposits,
        ];
            
        return $out;
    }
}
