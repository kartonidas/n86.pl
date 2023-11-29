<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Exceptions\ObjectNotExist;
use App\Models\Customer;
use App\Models\User;
use App\Traits\Sortable;

class CustomerController extends Controller
{
    use Sortable;
    
    public function list(Request $request)
    {
        User::checkAccess("customer:list");
        
        $request->validate([
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
            "sort" => "nullable",
            "order" => "nullable|integer",
        ]);
        
        $size = $request->input("size", config("api.list.size"));
        $page = $request->input("page", 1);
        
        $customers = Customer
            ::apiFields()->customer();
            
        $total = $customers->count();
        
        $orderBy = $this->getOrderBy($request, Customer::class, "name,asc");
        $customers = $customers->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $customers,
        ];
            
        return $out;
    }
    
    public function create(Request $request)
    {
        User::checkAccess("customer:create");
        
        $request->validate([
            "name" => "required|max:100",
            "street" => "nullable|max:80",
            "house_no" => "nullable|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "nullable|max:120",
            "zip" => "nullable|max:10",
            "nip" => ["nullable", "max:20", new \App\Rules\Nip],
        ]);
        
        $customer = new Customer;
        $customer->type = Customer::TYPE_CUSTOMER;
        $customer->name = $request->input("name");
        $customer->street = $request->input("street");
        $customer->house_no = $request->input("house_no");
        $customer->apartment_no = $request->input("apartment_no");
        $customer->city = $request->input("city");
        $customer->zip = $request->input("zip");
        $customer->nip = $request->input("nip");
        $customer->save();
        
        return $customer->id;
    }
    
    public function get(Request $request, $customerId)
    {
        User::checkAccess("customer:list");
        
        $customer = Customer::apiFields()->customer()->find($customerId);
        if(!$customer)
            throw new ObjectNotExist(__("Customer does not exist"));
        
        return $customer;
    }
    
    public function update(Request $request, $customerId)
    {
        User::checkAccess("customer:update");
        
        $customer = Customer::customer()->find($customerId);
        if(!$customer)
            throw new ObjectNotExist(__("Customer does not exist"));
        
        $rules = [
            "name" => "required|max:100",
            "street" => "nullable|max:80",
            "house_no" => "nullable|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "nullable|max:120",
            "zip" => "nullable|max:10",
            "nip" => ["nullable", "max:20", new \App\Rules\Nip],
        ];
        
        $validate = [];
        $updateFields = ["name", "street", "house_no", "apartment_no", "city", "zip", "nip"];
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
                $customer->{$field} = $request->input($field);
        }
        $customer->save();
        
        return true;
    }
    
    public function delete(Request $request, $customerId)
    {
        User::checkAccess("customer:delete");
        
        $customer = Customer::customer()->find($customerId);
        if(!$customer)
            throw new ObjectNotExist(__("Customer does not exist"));
        
        $customer->delete();
        return true;
    }
}