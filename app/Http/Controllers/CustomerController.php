<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Models\Country;
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
        
        $validated = $request->validate([
            "type" => ["required", Rule::in(Customer::TYPE_PERSON, Customer::TYPE_FIRM)],
            "name" => "required|max:100",
            "street" => "sometimes|max:80",
            "house_no" => "sometimes|max:20",
            "apartment_no" => "sometimes|max:20",
            "city" => "sometimes|max:120",
            "zip" => "sometimes|max:10",
            "country" => ["sometimes", Rule::in(Country::getAllowedCodes())],
            "pesel" => ["sometimes", "max:15", new \App\Rules\Pesel],
            "nip" => ["sometimes", "max:20", new \App\Rules\Nip],
            "comments" => "sometimes|max:5000",
            "send_sms" => "sometimes|boolean",
            "send_email" => "sometimes|boolean",
        ]);
        
        $customer = new Customer;
        $customer->role = Customer::ROLE_CUSTOMER;
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
        
        $customerContactFields = $request->validate([
            "contacts.email" => ["sometimes", "array", new \App\Rules\ContactEmail],
            "contacts.phone" => ["sometimes", "array", new \App\Rules\ContactPhone]
        ]);
        
        if(!empty($customerContactFields["contacts"]))
            $customer->updateContact($customerContactFields["contacts"]);
        
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
    
    public function update(Request $request, $customerId)
    {
        User::checkAccess("customer:update");
        
        $customer = Customer::customer()->find($customerId);
        if(!$customer)
            throw new ObjectNotExist(__("Customer does not exist"));
        
        $updateFields = $request->validate([
            "type" => ["required", Rule::in(Customer::TYPE_PERSON, Customer::TYPE_FIRM)],
            "name" => "required|max:100",
            "street" => "sometimes|max:80",
            "house_no" => "sometimes|max:20",
            "apartment_no" => "sometimes|max:20",
            "city" => "sometimes|max:120",
            "zip" => "sometimes|max:10",
            "country" => ["sometimes", Rule::in(Country::getAllowedCodes())],
            "pesel" => ["sometimes", "max:15", new \App\Rules\Pesel],
            "nip" => ["sometimes", "max:20", new \App\Rules\Nip],
            "comments" => "sometimes|max:5000",
            "send_sms" => "sometimes|boolean",
            "send_email" => "sometimes|boolean",
        ]);
        
        $updateContactFields = $request->validate([
            "contacts.email" => ["sometimes", "array", new \App\Rules\ContactEmail],
            "contacts.phone" => ["sometimes", "array", new \App\Rules\ContactPhone]
        ]);
        
        foreach($updateFields as $field => $value)
            $customer->{$field} = $value;
        $customer->save();
        
        if(!empty($updateContactFields["contacts"]))
            $customer->updateContact($updateContactFields["contacts"]);
        
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