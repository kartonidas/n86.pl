<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use App\Traits\Sortable;

class CustomerController extends Controller
{
    use Sortable;
    
    public function list(CustomerRequest $request)
    {
        User::checkAccess("customer:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $customers = Customer
            ::apiFields()->customer();
            
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["name"]))
                $customers->where("name", "LIKE", "%" . $validated["search"]["name"] . "%");
            if(!empty($validated["search"]["type"]))
                $customers->where("type", $validated["search"]["type"]);
            if(!empty($validated["search"]["city"]))
                $customers->where("city", "LIKE", "%" . $validated["search"]["city"] . "%");
            if(!empty($validated["search"]["pesel"]))
                $customers->where("pesel", "LIKE", "%" . $validated["search"]["pesel"] . "%");
            if(!empty($validated["search"]["nip"]))
                $customers->where("nip", "LIKE", "%" . $validated["search"]["nip"] . "%");
        }
            
        $total = $customers->count();
        
        $orderBy = $this->getOrderBy($request, Customer::class, "name,asc");
        $customers = $customers->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        foreach($customers as $i => $customer)
            $customers[$i]->can_delete = $customer->canDelete();
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $customers,
        ];
            
        return $out;
    }
    
    public function create(StoreCustomerRequest $request)
    {
        User::checkAccess("customer:create");
        
        $validated = $request->validated();
        
        $customer = new Customer;
        $customer->role = Customer::ROLE_CUSTOMER;
        $customer->type = $validated["type"];
        $customer->name = $validated["name"];
        $customer->street = $validated["street"] ?? null;
        $customer->house_no = $validated["house_no"] ?? null;
        $customer->apartment_no = $validated["apartment_no"] ?? null;
        $customer->city = $validated["city"] ?? null;
        $customer->zip = $validated["zip"] ?? null;
        $customer->country = $validated["country"] ?? null;
        $customer->nip = $validated["nip"] ?? null;
        $customer->pesel = $validated["pesel"] ?? null;
        $customer->comments = $validated["comments"] ?? null;
        $customer->send_sms = $validated["send_sms"] ?? 0;
        $customer->send_email = $validated["send_email"] ?? 0;
        $customer->save();
        
        if(!empty($validated["contacts"]))
            $customer->updateContact($validated["contacts"]);
        
        return $customer->id;
    }
    
    public function get(Request $request, $customerId)
    {
        User::checkAccess("customer:list");
        
        $customer = Customer::apiFields()->customer()->find($customerId);
        if(!$customer)
            throw new ObjectNotExist(__("Customer does not exist"));
        
        $customer->contacts = $customer->getContacts();
        
        return $customer;
    }
    
    public function update(UpdateCustomerRequest $request, $customerId)
    {
        User::checkAccess("customer:update");
        
        $customer = Customer::customer()->find($customerId);
        if(!$customer)
            throw new ObjectNotExist(__("Customer does not exist"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
        {
            if($field == "contacts")
                continue;
            
            $customer->{$field} = $value;
        }
        $customer->save();
        
        if(!empty($validated["contacts"]))
            $customer->updateContact($validated["contacts"]);
        
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