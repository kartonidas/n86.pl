<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Libraries\Helper;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Tenant;
use App\Models\User;

class ItemController extends Controller
{
    public function settings(Request $request)
    {
        $out = [
            "customers" => Customer::apiFields()->orderBy("name", "ASC")->get(),
        ];
        
        return $out;
    }
    
    public function list(Request $request)
    {
        User::checkAccess("item:list");
        
        $validated = $request->validate([
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
            "search.customer_id" => "sometimes|integer"
        ]);
        
        $size = $request->input("size", config("api.list.size"));
        $page = $request->input("page", 1);
        
        $items = Item
            ::apiFields();
        
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["customer_id"]))
                $items->where("customer_id", $validated["search"]["customer_id"]);
        }
        
        $total = $items->count();
            
        $items = $items->take($size)
            ->skip(($page-1)*$size)
            ->orderBy("name", "ASC")
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
    
    public function create(Request $request)
    {
        User::checkAccess("item:create");
        
        $rule = [
            "type" => ["required", Rule::in(array_keys(Item::getTypes()))],
            "name" => "required|max:100",
            "street" => "required|max:80",
            "house_no" => "nullable|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
            "country" => ["sometimes", Rule::in(Country::getAllowedCodes())],
            "area" => "sometimes|required|numeric",
            "ownership_type" => ["required", Rule::in(array_keys(Item::getOwnershipTypes()))],
            "room_rental" => "sometimes|required|boolean",
            "num_rooms" => "sometimes|required|integer",
            "description" => "sometimes|max:5000",
            "default_rent" => "sometimes|required|numeric",
            "default_deposit" => "sometimes|required|numeric",
            "comments" => "sometimes|max:5000",
        ];
        
        if($request->input("ownership_type", null) == Item::OWNERSHIP_MANAGE)
        {
            $customers = Customer::pluck("id");
            $rule["customer_id"] = ["required", Rule::in($customers->all())];
        }
        
        $validated = $request->validate($rule);
        
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
        
        return $item;
    }
    
    public function update(Request $request, int $estateId)
    {
        User::checkAccess("item:update");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $rules = [
            "name" => "sometimes|required|max:100",
            "type" => ["sometimes", "required", Rule::in(array_keys(Item::getTypes()))],
            "street" => "sometimes|required|max:80",
            "house_no" => "sometimes|nullable|max:20",
            "apartment_no" => "sometimes|nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
            "country" => ["sometimes", Rule::in(Country::getAllowedCodes())],
            "area" => "sometimes|required|numeric",
            "ownership_type" => ["required", Rule::in(array_keys(Item::getOwnershipTypes()))],
            "room_rental" => "sometimes|required|boolean",
            "num_rooms" => "sometimes|required|integer",
            "description" => "sometimes|max:5000",
            "default_rent" => "sometimes|required|numeric",
            "default_deposit" => "sometimes|required|numeric",
            "comments" => "sometimes|max:5000",
        ];
        
        if($request->input("ownership_type", null) == Item::OWNERSHIP_MANAGE)
        {
            $customers = Customer::pluck("id");
            $rules["customer_id"] = ["required", Rule::in($customers->all())];
        }
        
        $updateFields = $request->validate($rules);
        foreach($updateFields as $field => $value)
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
    
    public function tenants(Request $request, int $estateId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $request->validate([
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
        ]);
        
        $size = $request->input("size", config("api.list.size"));
        $page = $request->input("page", 1);
        
        $tenants = $item->getTenantsQuery()->apiFields();
        $total = $tenants->count();
            
        $tenants = $tenants->take($size)
            ->skip(($page-1)*$size)
            ->orderBy("name", "ASC")
            ->get();
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $tenants,
        ];
            
        return $out;
    }
    
    public function tenantCreate(Request $request, int $estateId)
    {
        User::checkAccess("tenant:create");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        $request->validate([
            "active" => "required|boolean",
            "name" => "required|max:100",
            "street" => "required|max:80",
            "house_no" => "required|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
        ]);
        
        if(!empty(config("api.tenants.allowed_document_types", [])))
        {
            $request->validate([
                "document_type" => ["nullable", Rule::in(array_keys(config("api.tenants.allowed_document_types")))],
                "document_number" => "nullable|max:100",
            ]);
        }
        
        $tenant = $item->createTenant($request->all());
        return $tenant->id;
    }
    
    public function tenantGet(Request $request, int $estateId, int $tenantId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
        return $item->getTenant();
    }
    
    public function tenantUpdate(Request $request, int $estateId, int $tenantId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
    }
    
    public function tenantDelete(Request $request, int $estateId, int $tenantId)
    {
        User::checkAccess("item:list");
        
        $item = Item::find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
    }
    
    public function tenantTerminate(Request $request, int $estateId, int $tenantId)
    {
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