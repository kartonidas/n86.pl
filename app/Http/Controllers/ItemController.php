<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Libraries\Helper;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Item;
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
    
    public function list(Request $request)
    {
        User::checkAccess("item:list");
        
        $validated = $request->validate([
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
            "sort" => "nullable",
            "order" => "nullable|integer",
            "search.customer_id" => "sometimes|integer",
            "search.name" => "nullable|string",
            "search.type" => ["nullable", Rule::in(array_keys(Item::getTypes()))],
        ]);
        
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
        }
        
        $total = $items->count();
        
        $orderBy = $this->getOrderBy($request, Item::class, "name,asc");
        $items = $items->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
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
    
    public function get(Request $request, int $estateId)
    {
        User::checkAccess("item:list");
        
        $item = Item::apiFields()->find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $item->customer = $item->getCustomer();
        $item->current_rental = $item->getCurrentRental();
        
        return $item;
    }
    
    public function update(UpdateItemRequest $request, int $estateId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
            $item->{$field} = $value;
        $item->save();
        
        return true;
    }
    
    public function delete(Request $request, int $estateId)
    {
        User::checkAccess("item:delete");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $item->delete();
        return true;
    }
    
    public function bills(Request $request, int $estateId)
    {
    }
    
    public function billCreate(Request $request, int $estateId)
    {
    }
    
    public function billGet(Request $request, int $estateId, int $billId)
    {
    }
    
    public function billUpdate(Request $request, int $estateId, int $billId)
    {
    }
    
    public function billDelete(Request $request, int $estateId, int $billId)
    {
    }
    
    public function billPaid(Request $request, int $estateId, int $billId)
    {
    }
    
    public function billUnpaid(Request $request, int $estateId, int $billId)
    {
    }
    
    public function fees(Request $request, int $estateId)
    {
    }
    
    public function feeCreate(Request $request, int $estateId)
    {
    }
    
    public function feeGet(Request $request, int $estateId, int $feeId)
    {
    }
    
    public function feeUpdate(Request $request, int $estateId, int $feeId)
    {
    }
    
    public function feeDelete(Request $request, int $estateId, int $feeId)
    {
    }
}