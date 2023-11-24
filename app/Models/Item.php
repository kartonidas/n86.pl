<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exceptions\ObjectNotExist;
use App\Models\ItemTenant;
use App\Models\Tenant;

class Item extends Model
{
    use SoftDeletes;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $casts = [
        "area" => "float",
        "default_rent" => "float",
        "default_deposit" => "float",
    ];
    
    const TYPE_APARTMENT = "apartment";
    const TYPE_HOUSE = "house";
    const TYPE_COMMERCIAL = "commercial";
    const TYPE_ROOM = "room";
    
    public static function getTypes()
    {
        return [
            self::TYPE_APARTMENT => __("Apartment"),
            self::TYPE_HOUSE => __("House"),
            self::TYPE_COMMERCIAL => __("Commercial"),
            self::TYPE_ROOM => __("Room"),
        ];
    }
    
    public function scopeApiFields(Builder $query): void
    {
        $query->select(
            "id",
            "type",
            "customer_id",
            "name",
            "street",
            "house_no",
            "apartment_no",
            "city",
            "zip",
            "area",
            "ownership",
            "room_rental",
            "num_rooms",
            "description",
            "default_rent",
            "default_deposit",
            "hidden",
            "created_at"
        );
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