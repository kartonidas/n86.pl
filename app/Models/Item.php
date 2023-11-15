<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ObjectNotExist;
use App\Models\ItemTenant;
use App\Models\Tenant;

class Item extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    public function scopeApiFields(Builder $query): void
    {
        $query->select("id", "type", "active", "name", "street", "house_no", "apartment_no", "city", "zip", "created_at");
    }
    
    public function getTenantsQuery()
    {
        $tenantIds = [-1];
        $itemTenants = ItemTenant::where("item_id", $this->id)->get();
        if(!$itemTenants->isEmpty())
        {
            foreach($itemTenants as $itemTenant)
                $tenantIds[] = $itemTenant->tenant_id;
        }
        
        return Tenant::whereIn("id", $tenantIds);
    }
    
    public function getTenant($tenantId)
    {
        $itemTenant = ItemTenant::where("item_id", $this->id)->where("tenant_id", $tenantId)->first();
        if(!$itemTenant)
            throw new ObjectNotExist(__("Tenant does not exist"));
        
        return Tenant::where("id", $tenantId)->apiFields()->first();
    }
    
    public function createTenant($tenantData)
    {
        $tenant = new Tenant;
        $tenant->active = $tenantData["active"] ?? false;
        $tenant->name = $tenantData["name"];
        $tenant->street = $tenantData["street"];
        $tenant->house_no = $tenantData["house_no"];
        $tenant->apartment_no = $tenantData["apartment_no"] ?? "";
        $tenant->city = $tenantData["city"];
        $tenant->zip = $tenantData["zip"];
        $tenant->document_type = $tenantData["document_type"] ?? null;
        $tenant->document_number = $tenantData["document_number"] ?? null;
        $tenant->save();
        
        $itemTenant = new ItemTenant;
        $itemTenant->item_id = $this->id;
        $itemTenant->tenant_id = $tenant->id;
        $itemTenant->save();
        
        return $tenant;
    }
}