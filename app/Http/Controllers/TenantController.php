<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\TenantRequest;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use App\Traits\Sortable;

class TenantController extends Controller
{
    use Sortable;
    
    public function list(TenantRequest $request)
    {
        User::checkAccess("tenant:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $tenants = Customer::tenant();
            
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["name"]))
                $tenants->where("name", "LIKE", "%" . $validated["search"]["name"] . "%");
            if(!empty($validated["search"]["type"]))
                $tenants->where("type", $validated["search"]["type"]);
            if(!empty($validated["search"]["pesel_nip"])) {
                $tenants->where(function($q) use($validated) {
                    $q
                        ->where("pesel", "LIKE", "%" . $validated["search"]["pesel_nip"] . "%")
                        ->orWhere("nip", "LIKE", "%" . $validated["search"]["pesel_nip"] . "%");
                });
            }
            if(!empty($validated["search"]["address"]))
            {
                $searchItemAddress = array_filter(explode(" ", $validated["search"]["address"]));
                $tenants->where(function($q) use($searchItemAddress) {
                    $q
                        ->where("street", "REGEXP", implode("|", $searchItemAddress))
                        ->orWhere("city", "REGEXP", implode("|", $searchItemAddress));
                });
            }
        }
            
        $total = $tenants->count();
        
        $orderBy = $this->getOrderBy($request, Customer::class, "name,asc");
        $tenants = $tenants->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        foreach($tenants as $i => $tenant)
            $tenants[$i]->can_delete = $tenant->canDelete();
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $tenants,
        ];
            
        return $out;
    }
    
    public function validateData(StoreTenantRequest $request)
    {
        return true;
    }
    
    public function create(StoreTenantRequest $request)
    {
        User::checkAccess("tenant:create");
        
        $validated = $request->validated();
        
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
        $tenant->regon = $validated["regon"] ?? null;
        $tenant->pesel = $validated["pesel"] ?? null;
        $tenant->document_type = $validated["document_type"] ?? null;
        $tenant->document_number = $validated["document_number"] ?? null;
        $tenant->document_extra = $validated["document_extra"] ?? null;
        $tenant->comments = $validated["comments"] ?? null;
        $tenant->send_sms = $validated["send_sms"] ?? 0;
        $tenant->send_email = $validated["send_email"] ?? 0;
        $tenant->save();
        
        if(!empty($validated["contacts"]))
            $tenant->updateContact($validated["contacts"]);
        
        return $tenant->id;
    }
    
    public function get(Request $request, $tenantId)
    {
        User::checkAccess("tenant:list");
        
        $tenant = Customer::tenant()->find($tenantId);
        if(!$tenant)
            throw new ObjectNotExist(__("Tenant does not exist"));
        
        $tenant->contacts = $tenant->getContacts();
        
        return $tenant;
    }
    
    public function update(UpdateTenantRequest $request, $tenantId)
    {
        User::checkAccess("tenant:update");
        
        $tenant = Customer::tenant()->find($tenantId);
        if(!$tenant)
            throw new ObjectNotExist(__("Tenant does not exist"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
        {
            if($field == "contacts")
                continue;
            
            $tenant->{$field} = $value;
        }
        $tenant->save();
        
        if(!empty($validated["contacts"]))
            $tenant->updateContact($validated["contacts"]);
        
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