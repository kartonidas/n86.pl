<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Libraries\Helper;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Tenant;
use App\Models\User;

class ItemController extends Controller
{
    public function settings(Request $request)
    {
        $getCustomer = User::checkAccess("customer:list", false);
        $out = [
            "types" => Helper::optionToArray(Item::getTypes()),
            "default_type" => Item::TYPE_APARTMENT,
            "customer_access" => $getCustomer,
        ];
        
        if($getCustomer)
            $out["customers"] = Customer::apiFields()->orderBy("name", "ASC")->get();
        
        return $out;
    }
    
    public function list(Request $request)
    {
        User::checkAccess("item:list");
        
        $request->validate([
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
        ]);
        
        $size = $request->input("size", config("api.list.size"));
        $page = $request->input("page", 1);
        
        $items = Item
            ::apiFields();
            
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
        
        $request->validate([
            "name" => "required|max:100",
            "type" => ["required", Rule::in(array_keys(Item::getTypes()))],
            "street" => "required|max:80",
            "house_no" => "nullable|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
            "area" => "sometimes|required|numeric",
            "ownership" => "sometimes|required|boolean",
            "room_rental" => "sometimes|required|boolean",
            "num_rooms" => "sometimes|required|integer",
            "description" => "sometimes|max:5000",
            "default_rent" => "sometimes|required|numeric",
            "default_deposit" => "sometimes|required|numeric",
        ]);
        
        $hasCustomerAccess = User::checkAccess("customer:list", false);
        if($hasCustomerAccess && !$request->input("ownership"))
        {
            $customers = Customer::pluck("id");
            $request->validate([
                "customer_id" => ["required", Rule::in($customers->all())]
            ]);
        }
        
        $item = new Item;
        $item->type = $request->input("type");
        $item->name = $request->input("name");
        $item->street = $request->input("street");
        $item->house_no = $request->input("house_no");
        $item->apartment_no = $request->input("apartment_no");
        $item->city = $request->input("city");
        $item->zip = $request->input("zip");
        $item->area = $request->input("area");
        $item->ownership = $hasCustomerAccess ? $request->input("ownership", 1) : 1;
        $item->customer_id = $hasCustomerAccess ? $request->input("customer_id", 0) : 0;
        $item->room_rental = $request->input("room_rental", 0);
        $item->num_rooms = $request->input("num_rooms");
        $item->description = $request->input("description");
        $item->default_rent = $request->input("default_rent");
        $item->default_deposit = $request->input("default_deposit");
        $item->save();
        
        return $item->id;
    }
    
    public function get(Request $request, int $estateId)
    {
        User::checkAccess("item:list");
        
        $item = Item::apiFields()->find($estateId);
        if(!$item)
            throw new ObjectNotExist(__("Item does not exist"));
        
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
            "area" => "sometimes|required|numeric",
            "ownership" => "sometimes|required|boolean",
            "room_rental" => "sometimes|required|boolean",
            "num_rooms" => "sometimes|required|integer",
            "description" => "sometimes|max:5000",
            "default_rent" => "sometimes|required|numeric",
            "default_deposit" => "sometimes|required|numeric",
        ];
        
        $hasCustomerAccess = User::checkAccess("customer:list", false);
        if($hasCustomerAccess && !$request->input("ownership"))
        {
            $customers = Customer::pluck("id");
            $rules["customer_id"] = ["required", Rule::in($customers->all())];
        }
        
        $updateFields = $request->validate($rules);
        foreach($updateFields as $field => $value)
        {
            if($request->has($field))
                $item->{$field} = $value;
        }
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