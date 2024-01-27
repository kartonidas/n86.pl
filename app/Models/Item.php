<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Libraries\Helper;
use App\Models\Customer;
use App\Models\Config;
use App\Models\ItemBill;
use App\Models\ItemTenant;
use App\Models\Rental;
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
        "balance" => "float",
    ];
    protected $hidden = ["uuid"];
    
    const TYPE_APARTMENT = "apartment";
    const TYPE_HOUSE = "house";
    const TYPE_COMMERCIAL = "commercial";
    const TYPE_ROOM = "room";
    const OWNERSHIP_PROPERTY = "property";
    const OWNERSHIP_MANAGE = "manage";
    
    public static $sortable = ["name"];
    public static $defaultSortable = ["name", "asc"];
    
    public static function getTypes()
    {
        return [
            self::TYPE_APARTMENT => __("Apartment"),
            self::TYPE_HOUSE => __("House"),
            self::TYPE_COMMERCIAL => __("Commercial"),
            self::TYPE_ROOM => __("Room"),
        ];
    }
    
    public static function getOwnershipTypes()
    {
        return [
            self::OWNERSHIP_PROPERTY => __("My property"),
            self::OWNERSHIP_MANAGE => __("I manage on behalf of the client"),
        ];
    }
    
    public function canDelete()
    {
        $c1 = Rental::where("item_id", $this->id)->count();
            
        if($c1)
            return false;
        
        return true;
    }
    
    public function delete()
    {
        if(!$this->canDelete())
            throw new InvalidStatus(__("Cannot delete object"));
        
        return parent::delete();
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
        
        return Tenant::where("id", $tenantId)->first();
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
    
    public function getCustomer()
    {
        if($this->ownership_type == self::OWNERSHIP_MANAGE)
            return $this->customer()->first();
        return null;
    }
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function getCurrentRental()
    {
        $rental = Rental
            ::where("uuid", $this->uuid)
            ->where("item_id", $this->id)
            ->where("status", Rental::STATUS_CURRENT)
            ->withoutGlobalScopes()
            ->first();
            
        if($rental)
        {
            $rental->prepareViewData();
            $rental->tenant = $rental->getTenant();
        }
        
        return $rental;
    }
    
    public function setRentedFlag()
    {
        $cnt = Rental::where("status", Rental::STATUS_CURRENT)->where("item_id", $this->id)->withoutGlobalScopes()->count();
        $this->rented = $cnt > 0 ? 1 : 0;
        $this->waiting_rentals = Rental::where("status", Rental::STATUS_WAITING)->where("item_id", $this->id)->withoutGlobalScopes()->count();
        $this->saveQuietly();
    }
    
    public static function recalculate(Item $item)
    {
        if($item->customer_id > 0)
        {
            $totalItems = self::where("customer_id", $item->customer_id)->where("hidden", 0)->count();
            $customer = Customer::find($item->customer_id);
            if($customer)
            {
                $customer->total_items = $totalItems;
                $customer->saveQuietly();
            }
        }
    }
    
    public function addBill(array $data) : ItemBill
    {
        $bill = new ItemBill;
        $bill->item_id = $this->id;
        $bill->rental_id = $data["rental_id"] ?? 0;
        $bill->bill_type_id = $data["bill_type_id"];
        $bill->payment_date = Helper::setDateTime($data["payment_date"], "23:59:59", true);
        $bill->cost = $data["cost"];
        $bill->recipient_name = $data["recipient_name"] ?? null;
        $bill->recipient_desciption = $data["recipient_desciption"] ?? null;
        $bill->recipient_bank_account = $data["recipient_bank_account"] ?? null;
        $bill->source_document_number = $data["source_document_number"] ?? null;
        $bill->source_document_date = !empty($data["source_document_date"]) ? Helper::setDateTime($data["source_document_date"], "12:00:00", true) : null;
        $bill->comments = $data["comments"] ?? null;
        $bill->save();
        
        return $bill;
    }
    
    public function getOwner()
    {
        if($this->customer_id)
        {
            $customer = Customer::find($this->customer_id);
            if(!$customer)
                throw new ObjectNotExist(__("Customer does not exists"));
            
            return $customer;
        }
        else
        {
            $config = Config::getConfig("basic");
            $out = new \stdClass();
            
            if(!empty($config))
            {
                foreach($config as $field => $value)
                {
                    if(substr($field, 0, 6) == "owner_")
                        $out->{substr($field, 6)} = $value;
                }
            }
            
            return $out;
        }
    }
    
    public function scopeActive(Builder $query): void
    {
        $query->whereRaw("1=1");
    }
}