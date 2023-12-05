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

class TenantController extends Controller
{
    use Sortable;
    
    public function list(Request $request)
    {
        User::checkAccess("tenant:list");
        
        $request->validate([
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
            "sort" => "nullable",
            "order" => "nullable|integer",
        ]);
        
        $size = $request->input("size", config("api.list.size"));
        $page = $request->input("page", 1);
        
        $tenants = Customer
            ::apiFields()->tenant();
            
        $total = $tenants->count();
        
        $orderBy = $this->getOrderBy($request, Customer::class, "name,asc");
        $tenants = $tenants->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
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
    
    public function create(Request $request)
    {
        User::checkAccess("tenant:create");
        
        $rules = [
            "type" => ["required", Rule::in(Customer::TYPE_PERSON, Customer::TYPE_FIRM)],
            "name" => "required|max:100",
            "street" => "nullable|max:80",
            "house_no" => "nullable|max:20",
            "apartment_no" => "nullable|max:20",
            "city" => "nullable|max:120",
            "zip" => "nullable|max:10",
            "country" => ["sometimes", Rule::in(Country::getAllowedCodes())],
            "comments" => "sometimes|max:5000",
            "send_sms" => "sometimes|boolean",
            "send_email" => "sometimes|boolean",
        ];
        
        if($request->input("type", null) == Customer::TYPE_PERSON)
            $rules["pesel"] = ["sometimes", "max:15", new \App\Rules\Pesel];
        elseif($request->input("type", null) == Customer::TYPE_FIRM)
            $rules["nip"] = ["sometimes", "max:20", new \App\Rules\Nip];
        
        $validated = $request->validate($rules);
        
        $tenant = new Customer;
        $tenant->role = Customer::ROLE_TENANT;
        $tenant->type = $validated["type"];
        $tenant->name = $validated["name"];
        $tenant->street = $validated["street"] ?? null;
        $tenant->house_no = $validated["house_no"] ?? null;
        $tenant->apartment_no = $validated["apartment_no"] ?? null;
        $tenant->city = $validated["city"] ?? null;
        $tenant->zip = $validated["zip"] ?? null;
        $tenant->country = $validated["country"] ?? null;
        $tenant->nip = $validated["nip"] ?? null;
        $tenant->pesel = $validated["pesel"] ?? null;
        $tenant->comments = $validated["comments"] ?? null;
        $tenant->send_sms = $validated["send_sms"] ?? 0;
        $tenant->send_email = $validated["send_email"] ?? 0;
        $tenant->save();
        
        return $tenant->id;
    }
    
    public function get(Request $request, $tenantId)
    {
        User::checkAccess("tenant:list");
        
        $tenant = Customer::apiFields()->tenant()->find($tenantId);
        if(!$tenant)
            throw new ObjectNotExist(__("Tenant does not exist"));
        
        $tenant->contacts = $tenant->getContacts();
        
        return $tenant;
    }
    
    public function update(Request $request, $tenantId)
    {
        User::checkAccess("tenant:update");
        
        $tenant = Customer::tenant()->find($tenantId);
        if(!$tenant)
            throw new ObjectNotExist(__("Tenant does not exist"));
        
        $rules = [
            "type" => ["required", Rule::in(Customer::TYPE_PERSON, Customer::TYPE_FIRM)],
            "name" => "required|max:100",
            "street" => "sometimes|max:80",
            "house_no" => "sometimes|max:20",
            "apartment_no" => "sometimes|max:20",
            "city" => "sometimes|max:120",
            "zip" => "sometimes|max:10",
            "country" => ["sometimes", Rule::in(Country::getAllowedCodes())],
            "comments" => "sometimes|max:5000",
            "send_sms" => "sometimes|boolean",
            "send_email" => "sometimes|boolean",
        ];
        
        if($request->input("type", null) == Customer::TYPE_PERSON)
            $rules["pesel"] = ["sometimes", "max:15", new \App\Rules\Pesel];
        elseif($request->input("type", null) == Customer::TYPE_FIRM)
            $rules["nip"] = ["sometimes", "max:20", new \App\Rules\Nip];
        
        $updateFields = $request->validate($rules);
        
        $updateContactFields = $request->validate([
            "contacts.email" => ["sometimes", "array", new \App\Rules\ContactEmail],
            "contacts.phone" => ["sometimes", "array", new \App\Rules\ContactPhone]
        ]);
        
        foreach($updateFields as $field => $value)
            $tenant->{$field} = $value;
        $tenant->save();
        
        if(!empty($updateContactFields["contacts"]))
            $tenant->updateContact($updateContactFields["contacts"]);
        
        return true;
    }
    
    public function delete(Request $request, $tenantId)
    {
        User::checkAccess("tenant:delete");
        
        $tenant = Customer::tenant()->find($tenantId);
        if(!$tenant)
            throw new ObjectNotExist(__("Tenant does not exist"));
        
        $tenant->delete();
        return true;
    }
    
    public function history(Request $request, $tenantId)
    {
    }
}