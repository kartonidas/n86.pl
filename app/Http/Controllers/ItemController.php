<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Models\Item;
use App\Models\Tenant;
use App\Models\User;

class ItemController extends Controller
{
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
            "type" => ["required", Rule::in("estate")],
            "active" => "required|boolean",
            "name" => "required|max:100",
            "street" => "required|max:80",
            "house_no" => "required|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
        ]);
        
        $item = new Item;
        $item->type = $request->input("type");
        $item->active = $request->input("active", 0);
        $item->name = $request->input("name");
        $item->street = $request->input("street");
        $item->house_no = $request->input("house_no");
        $item->apartment_no = $request->input("apartment_no", "");
        $item->city = $request->input("city");
        $item->zip = $request->input("zip");
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
            "active" => "required|boolean",
            "name" => "required|max:100",
            "street" => "required|max:80",
            "house_no" => "required|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "required|max:120",
            "zip" => "required|max:10",
        ];
        
        $validate = [];
        $updateFields = ["active", "name", "street", "house_no", "apartment_no", "city", "zip"];
        foreach($updateFields as $field)
        {
            if($request->has($field))
            {
                if(!empty($rules[$field]))
                    $validate[$field] = $rules[$field];
            }
        }
        
        if(!empty($validate))
            $request->validate($validate);
        
        foreach($updateFields as $field)
        {
            if($request->has($field))
                $item->{$field} = $request->input($field);
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