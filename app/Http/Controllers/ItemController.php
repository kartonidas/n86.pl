<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Http\Requests\BillPaymentRequest;
use App\Http\Requests\ItemRequest;
use App\Http\Requests\ItemBillRequest;
use App\Http\Requests\ItemCyclicalFeeCostRequest;
use App\Http\Requests\ItemCyclicalFeeRequest;
use App\Http\Requests\StoreItemBillRequest;
use App\Http\Requests\StoreItemCyclicalFeeCostRequest;
use App\Http\Requests\StoreItemCyclicalFeeRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemBillRequest;
use App\Http\Requests\UpdateItemCyclicalFeeCostRequest;
use App\Http\Requests\UpdateItemCyclicalFeeRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Libraries\Helper;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\ItemCyclicalFee;
use App\Models\ItemCyclicalFeeCost;
use App\Models\Rental;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\Sortable;

class ItemController extends Controller
{
    use Sortable;
    
    public function settings(Request $request)
    {
        $out = [
            "customers" => Customer::customer()->orderBy("name", "ASC")->get(),
        ];
        
        return $out;
    }
    
    public function list(ItemRequest $request)
    {
        User::checkAccess("item:list");
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $items = Item::whereRaw("1=1");
        
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["name"]))
                $items->where("name", "LIKE", "%" . $validated["search"]["name"] . "%");
            if(!empty($validated["search"]["type"]))
                $items->where("type", $validated["search"]["type"]);
            if(!empty($validated["search"]["customer_id"]))
                $items->where("customer_id", $validated["search"]["customer_id"]);
            if(!empty($validated["search"]["address"]))
            {
                $searchItemAddress = array_filter(explode(" ", $validated["search"]["address"]));
                $items->where(function($q) use($searchItemAddress) {
                    $q
                        ->where("street", "REGEXP", implode("|", $searchItemAddress))
                        ->orWhere("city", "REGEXP", implode("|", $searchItemAddress));
                });
            }
            if(isset($validated["search"]["rented"]))
                $items->where("rented", !empty($validated["search"]["rented"]) ? 1 : 0);
        }
        
        if(empty($validated["search"]["mode"]))
        {
            $items->where("mode", "!=", Item::MODE_ARCHIVED);
        }
        else
        {
            if($validated["search"]["mode"] !== "all")
                $items->where("mode", $validated["search"]["mode"]);
        }
        
        if(!empty($validated["search"]["ownership_type"]))
        {
            switch($validated["search"]["ownership_type"])
            {
                case "property": break;
                    $items->where("ownership_type", Item::OWNERSHIP_PROPERTY);
                case "manage":
                    $items->where("ownership_type", Item::OWNERSHIP_MANAGE);
                break;
            }
        }
        
        $total = $items->count();
        
        $orderBy = $this->getOrderBy($request, Item::class, "name,asc");
        $items = $items->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        foreach($items as $i => $item)
        {
            $items[$i]->can_delete = $item->canDelete();
            $items[$i]->can_archive = $item->canArchive();
            $items[$i]->can_lock = $item->canLock();
            $items[$i]->can_unlock = $item->canUnlock();
            $items[$i]->can_edit = $item->canEdit();
            $items[$i]->can_add_rental = $item->canAddRental();
            $items[$i]->rentals = $item->getRentalInfo();
            $items[$i]->customer = $item->getCustomer();
        }
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $items,
        ];
            
        return $out;
    }
    
    public function validateData(StoreItemRequest $request)
    {
        return true;
    }
    
    public function create(StoreItemRequest $request)
    {
        User::checkAccess("item:create");
        
        $validated = $request->validated();
        
        $item = new Item;
        $item->type = $validated["type"];
        $item->name = $validated["name"];
        $item->street = $validated["street"];
        $item->house_no = $validated["house_no"] ?? null;
        $item->apartment_no = $validated["apartment_no"] ?? null;
        $item->city = $validated["city"];
        $item->zip = $validated["zip"];
        $item->country = $validated["country"] ?? null;
        $item->area = $validated["area"] ?? null;
        $item->ownership_type = $validated["ownership_type"];
        $item->customer_id = $validated["ownership_type"] == Item::OWNERSHIP_MANAGE ? ($validated["customer_id"] ?? null) : null;
        $item->room_rental = $validated["room_rental"] ?? 0;
        $item->num_rooms = $validated["num_rooms"] ?? null;
        $item->description = $validated["description"] ?? null;
        $item->default_rent = $validated["default_rent"] ?? null;
        $item->default_deposit = $validated["default_deposit"] ?? null;
        $item->comments = $validated["comments"] ?? null;
        $item->save();
        
        return $item->id;
    }
    
    public function get(Request $request, int $itemId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $item->customer = $item->getCustomer();
        $item->current_rental = $item->getCurrentRental();
        $item->can_archive = $item->canArchive();
        $item->can_lock = $item->canLock();
        $item->can_unlock = $item->canUnlock();
        $item->can_edit = $item->canEdit();
        $item->can_add_rental = $item->canAddRental();
        
        return $item;
    }
    
    public function update(UpdateItemRequest $request, int $itemId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot update item"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
            $item->{$field} = $value;
        $item->save();
        
        return true;
    }
    
    public function delete(Request $request, int $itemId)
    {
        User::checkAccess("item:delete");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $item->delete();
        return true;
    }
    
    public function bills(ItemBillRequest $request, int $itemId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $itemBills = ItemBill
            ::where("item_id", $itemId);
        
        if(!empty($validated["search"]))
        {
            // todo
        }
        
        $total = $itemBills->count();
        
        $itemBills = $itemBills->take($size)
            ->skip(($page-1)*$size)
            ->orderBy("paid", "ASC")
            ->orderByRaw("CASE WHEN paid = 1 THEN payment_date ELSE -payment_date END DESC")
            ->get();
            
        foreach($itemBills as $i => $itemBill)
        {
            $itemBills[$i]->prepareViewData();
            $itemBills[$i]->can_delete = $itemBill->canDelete();
            $itemBills[$i]->bill_type = $itemBill->getBillType();
            $itemBills[$i]->out_off_date = $itemBill->isOutOfDate();
            $itemBills[$i]->rental = $itemBill->getRental();
        }
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $itemBills,
        ];
            
        return $out;
    }
    
    public function billCreate(StoreItemBillRequest $request, int $itemId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot add bill. Item was archived"));
        
        $validated = $request->validated();
        
        if(!empty($validated["charge_current_tenant"]))
        {
            $rental = $item->getCurrentRental();
            if($rental)
                $validated["rental_id"] = $rental->id;
        }
        
        $bill = DB::transaction(function () use($item, $validated) {
            return $item->addBill($validated);
        });
        
        return $bill->id;
    }
    
    public function billGet(Request $request, int $itemId, int $billId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->item_id != $item->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        $bill->prepareViewData();
        $bill->bill_type = $bill->getBillType();
        $bill->out_off_date = $bill->isOutOfDate();
        
        return $bill;
    }
    
    public function billUpdate(UpdateItemBillRequest $request, int $itemId, int $billId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->item_id != $item->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        if($bill->paid)
            throw new InvalidStatus(__("Cannot edit paid bill"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot edit bill. Item was archived"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
        {
            if(!empty($value) && ($field == "payment_date" || $field == "source_document_date"))
                $value = Helper::setDateTime($value, $field == "source_document_date" ? "12:00:00" : "23:59:59", true);
            $bill->{$field} = $value;
        }
        $bill->save();
        
        return true;
    }
    
    public function billDelete(Request $request, int $itemId, int $billId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->item_id != $item->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot delete bill. Item was archived"));
        
        $bill->delete();
        return true;
    }
    
    public function billPaid(Request $request, int $itemId, int $billId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->item_id != $item->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot paid bill. Item was archived"));
        
        return $bill->paid();
    }
    
    public function billUnpaid(Request $request, int $itemId, int $billId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->item_id != $item->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot unpaid bill. Item was archived"));
        
        return $bill->unpaid();
    }
    
    public function billPayment(BillPaymentRequest $request, int $itemId, int $billId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $bill = ItemBill::find($billId);
        if(!$bill || $bill->item_id != $item->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        if($bill->paid)
            throw new InvalidStatus(__("Bill is already paid"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot paid bill. Item was archived"));
        
        $validated = $request->validated();
        
        switch($validated["type"])
        {
            case "deposit":
                if(!$bill->rental_id)
                    throw new InvalidStatus(__("Cannot charge the tenant"));
                
                $bill->deposit($validated["cost"], $validated["paid_date"], $validated["payment_method"]);
            break;
        
            case "setpaid":
                $bill->paid();
            break;
        }
        
        return true;
    }
    
    public function fees(ItemCyclicalFeeRequest $request, int $itemId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $itemFees = ItemCyclicalFee
            ::where("item_id", $itemId);
        
        if(!empty($validated["search"]))
        {
            // todo
        }
        
        $total = $itemFees->count();
        
        $orderBy = $this->getOrderBy($request, ItemCyclicalFee::class, "created_at,desc");
        $itemFees = $itemFees->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        foreach($itemFees as $i => $itemFee)
        {
            $itemFees[$i]->can_delete = $itemFee->canDelete();
            $itemFees[$i]->bill_type = $itemFee->getBillType();
        }
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $itemFees,
        ];
            
        return $out;
    }
    
    public function feeCreate(StoreItemCyclicalFeeRequest $request, int $itemId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot add fees. Item was archived"));
        
        $validated = $request->validated();
        
        $cyclicalFee = DB::transaction(function () use($item, $validated) {
            $cyclicalFee = new ItemCyclicalFee;
            $cyclicalFee->item_id = $item->id;
            $cyclicalFee->bill_type_id = $validated["bill_type_id"];
            $cyclicalFee->payment_day = $validated["payment_day"];
            $cyclicalFee->repeat_months = $validated["repeat_months"];
            $cyclicalFee->tenant_cost = $validated["tenant_cost"] ?? 0;
            $cyclicalFee->cost = $validated["cost"];
            $cyclicalFee->recipient_name = $validated["recipient_name"] ?? null;
            $cyclicalFee->recipient_desciption = $validated["recipient_desciption"] ?? null;
            $cyclicalFee->recipient_bank_account = $validated["recipient_bank_account"] ?? null;
            $cyclicalFee->comments = $validated["comments"] ?? null;
            $cyclicalFee->save();
            
            return $cyclicalFee;
        });
        
        return $cyclicalFee->id;
    }
    
    public function feeGet(Request $request, int $itemId, int $feeId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee || $fee->item_id != $item->id)
            throw new ObjectNotExist(__("Fee does not exist"));
        
        return $fee;
    }
    
    public function feeUpdate(UpdateItemCyclicalFeeRequest $request, int $itemId, int $feeId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee || $fee->item_id != $item->id)
            throw new ObjectNotExist(__("Fee does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot update fee. Item was archived"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
        {
            if(!empty($value) && $field == "source_document_date")
                $value = Helper::setDateTime($value, $field == "source_document_date" ? "12:00:00" : "00:00:00", true);
            $fee->{$field} = $value;
        }
        $fee->save();
        
        return true;
    }
    
    public function feeDelete(Request $request, int $itemId, int $feeId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee || $fee->item_id != $item->id)
            throw new ObjectNotExist(__("Fee does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot delete fee. Item was archived"));
        
        $fee->delete();
        return true;
    }
    
    public function feeCosts(ItemCyclicalFeeCostRequest $request, int $itemId, int $feeId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee)
            throw new ObjectNotExist(__("Cyclical fee does not exist"));
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $itemFeeCosts = ItemCyclicalFeeCost
            ::where("item_cyclical_fee_id", $feeId);
        
        $total = $itemFeeCosts->count();
        
        $orderBy = $this->getOrderBy($request, ItemCyclicalFeeCost::class, "from_time,desc");
        $itemFeeCosts = $itemFeeCosts->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        foreach($itemFeeCosts as $i => $itemFeeCost)
        {
            $itemFeeCosts[$i]->prepareViewData();
            $itemFeeCosts[$i]->can_delete = $itemFeeCost->canDelete();
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $itemFeeCosts,
        ];
            
        return $out;
    }
    
    public function feeCostCreate(StoreItemCyclicalFeeCostRequest $request, int $itemId, int $feeId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee)
            throw new ObjectNotExist(__("Cyclical fee does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot add cost. Item was archived"));
        
        $validated = $request->validated();
        
        $cnt = ItemCyclicalFeeCost
            ::where("item_cyclical_fee_id", $feeId)
            ->where("from_time", ">=", Helper::setDateTime($validated["from_time"], "00:00:00", true))
            ->where("from_time", "<=", Helper::setDateTime($validated["from_time"], "23:59:59", true))
            ->count();
            
        if($cnt > 0)
            throw new InvalidStatus(__("There is already a cost with a given date"));
        
        $cost = new ItemCyclicalFeeCost;
        $cost->item_cyclical_fee_id = $feeId;
        $cost->from_time = Helper::setDateTime($validated["from_time"], "00:00:00", true);
        $cost->cost = $validated["cost"];
        $cost->save();
        
        return $cost->id;
    }
    
    public function feeCostGet(Request $request, int $itemId, int $feeId, int $costId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee)
            throw new ObjectNotExist(__("Cyclical fee does not exist"));
        
        $cost = ItemCyclicalFeeCost::find($costId);
        if(!$cost || $cost->item_cyclical_fee_id != $feeId)
            throw new ObjectNotExist(__("Cost does not exist"));
        
        
        $cost->prepareViewData();
        
        return $cost;
    }
    
    public function feeCostUpdate(UpdateItemCyclicalFeeCostRequest $request, int $itemId, int $feeId, int $costId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee)
            throw new ObjectNotExist(__("Cyclical fee does not exist"));
        
        $cost = ItemCyclicalFeeCost::find($costId);
        if(!$cost || $cost->item_cyclical_fee_id != $feeId)
            throw new ObjectNotExist(__("Cost does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot update cost. Item was archived"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
        {
            if(!empty($value) && $field == "from_time")
                $value = Helper::setDateTime($value, "00:00:00", true);
            $cost->{$field} = $value;
        }
        $cost->save();
        
        return true;
    }
    
    public function feeCostDelete(Request $request, int $itemId, int $feeId, int $costId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $fee = ItemCyclicalFee::find($feeId);
        if(!$fee)
            throw new ObjectNotExist(__("Cyclical fee does not exist"));
        
        $cost = ItemCyclicalFeeCost::find($costId);
        if(!$cost || $cost->item_cyclical_fee_id != $feeId)
            throw new ObjectNotExist(__("Fee does not exist"));
        
        if(!$item->canEdit())
            throw new InvalidStatus(__("Cannot delete cost. Item was archived"));
        
        $cost->delete();
        return true;
    }
    
    public function archive(Request $request, int $itemId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        if(!$item->canArchive())
            throw new ObjectNotExist(__("Cannot archive item"));
        
        $item->archive();
        return true;
    }
    
    public function lock(Request $request, int $itemId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        if(!$item->canLock())
            throw new ObjectNotExist(__("Cannot lock item"));
        
        $item->lock();
        return true;
    }
    
    public function unlock(Request $request, int $itemId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        if(!$item->canUnlock())
            throw new ObjectNotExist(__("Cannot unlock item"));
        
        $item->unlock();
        return true;
    }
}