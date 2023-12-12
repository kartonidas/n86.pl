<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\ItemRequest;
use App\Http\Requests\ItemBillRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\StoreItemBillRequest;
use App\Http\Requests\UpdateItemBillRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Libraries\Helper;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\Sortable;

class ItemController extends Controller
{
    use Sortable;
    
    public function settings(Request $request)
    {
        $out = [
            "customers" => Customer::apiFields()->customer()->orderBy("name", "ASC")->get(),
        ];
        
        return $out;
    }
    
    public function list(ItemRequest $request)
    {
        User::checkAccess("item:list");
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $items = Item
            ::apiFields();
        
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
        
        $total = $items->count();
        
        $orderBy = $this->getOrderBy($request, Item::class, "name,asc");
        $items = $items->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        foreach($items as $i => $item)
            $items[$i]->can_delete = $item->canDelete();
            
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
        
        $item = Item::apiFields()->find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $item->customer = $item->getCustomer();
        $item->current_rental = $item->getCurrentRental();
        
        return $item;
    }
    
    public function update(UpdateItemRequest $request, int $itemId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
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
        User::checkAccess("item:update");
        
        $item = Item::find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $itemBills = ItemBill
            ::apiFields()
            ->where("item_id", $itemId);
        
        if(!empty($validated["search"]))
        {
            // todo
        }
        
        $total = $itemBills->count();
        
        $orderBy = $this->getOrderBy($request, ItemBill::class, "payment_date,desc");
        $itemBills = $itemBills->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        foreach($itemBills as $i => $itemBill)
        {
            $itemBills[$i]->can_delete = $itemBill->canDelete();
            $itemBills[$i]->bill_type = $itemBill->getBillType();
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
        
        $validated = $request->validated();
        $bill = $item->addBill($validated);
        
        return $bill->id;
    }
    
    public function billGet(Request $request, int $itemId, int $billId)
    {
        User::checkAccess("item:update");
        
        $item = Item::apiFields()->find($itemId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $bill = ItemBill::apiFields()->find($billId);
        if(!$bill || $bill->item_id != $item->id)
            throw new ObjectNotExist(__("Bill does not exist"));
        
        $bill->bill_type = $bill->getBillType();
        
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
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
        {
            if(!empty($value) && ($field == "payment_date" || $field == "source_document_date"))
                $value = strtotime($value);
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
        
        $bill->delete();
        return true;
    }
    
    //public function billPaid(Request $request, int $itemId, int $billId)
    //{
    //    User::checkAccess("item:update");
    //    
    //    $item = Item::find($itemId);
    //    if(!$item)
    //        throw new ObjectNotExist(__("Item does not exist"));
    //}
    //
    //public function billUnpaid(Request $request, int $itemId, int $billId)
    //{
    //    User::checkAccess("item:update");
    //    
    //    $item = Item::find($itemId);
    //    if(!$item)
    //        throw new ObjectNotExist(__("Item does not exist"));
    //}
    //
    //public function fees(Request $request, int $itemId)
    //{
    //}
    //
    //public function feeCreate(Request $request, int $itemId)
    //{
    //}
    //
    //public function feeGet(Request $request, int $itemId, int $feeId)
    //{
    //}
    //
    //public function feeUpdate(Request $request, int $itemId, int $feeId)
    //{
    //}
    //
    //public function feeDelete(Request $request, int $itemId, int $feeId)
    //{
    //}
}