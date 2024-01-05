<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Http\Requests\RentalBillRequest;
use App\Http\Requests\RentalRequest;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\StoreRentRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\TerminationRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Libraries\Helper;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\Rental;
use App\Traits\Sortable;
use App\Models\User;

class RentalController extends Controller
{
    use Sortable;
    
    public function list(RentalRequest $request)
    {
        User::checkAccess("rent:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $rentals = Rental::whereRaw("1=1");
        
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["item_id"]))
                $rentals->where("item_id", $validated["search"]["item_id"]);
            if(!empty($validated["search"]["tenant_id"]))
                $rentals->where("tenant_id", $validated["search"]["tenant_id"]);
            if(!empty($validated["search"]["status"]))
                $rentals->where("status", $validated["search"]["status"]);
                
            if(!empty($validated["search"]["item_name"]) || !empty($validated["search"]["item_address"]) || !empty($validated["search"]["item_type"]))
            {
                $items = Item::select("id");
                
                if(!empty($validated["search"]["item_name"]))
                    $items->where("name", "LIKE", "%" . $validated["search"]["item_name"] . "%");
                
                if(!empty($validated["search"]["item_address"]))
                {
                    $searchItemAddress = array_filter(explode(" ", $validated["search"]["item_address"]));
                    $items->where(function($q) use($searchItemAddress) {
                        $q
                            ->where("street", "REGEXP", implode("|", $searchItemAddress))
                            ->orWhere("city", "REGEXP", implode("|", $searchItemAddress));
                    });
                }
                
                if(!empty($validated["search"]["item_type"]))
                    $items->where("type", $validated["search"]["item_type"]);
                
                $itemIds = $items->pluck("id")->all();
                $rentals->whereIn("item_id", !empty($itemIds) ? $itemIds : [-1]);
            }
            
            if(!empty($validated["search"]["tenant_name"]) || !empty($validated["search"]["tenant_address"]) || !empty($validated["search"]["tenant_type"]))
            {
                $tenants = Customer::tenant();
                
                if(!empty($validated["search"]["tenant_name"]))
                    $tenants->where("name", "LIKE", "%" . $validated["search"]["tenant_name"] . "%");
                
                if(!empty($validated["search"]["tenant_address"]))
                {
                    $searchCustomerAddress = array_filter(explode(" ", $validated["search"]["tenant_address"]));
                    $tenants->where(function($q) use($searchCustomerAddress) {
                        $q
                            ->where("street", "REGEXP", implode("|", $searchCustomerAddress))
                            ->orWhere("city", "REGEXP", implode("|", $searchCustomerAddress));
                    });
                }
                
                if(!empty($validated["search"]["tenant_type"]))
                    $tenants->where("type", $validated["search"]["tenant_type"]);
                
                $tenantIds = $tenants->pluck("id")->all();
                $rentals->whereIn("tenant_id", !empty($tenantIds) ? $tenantIds : [-1]);
            }
            
            if(!empty($validated["search"]["start"]))
            {
                $start = Helper::setDateTime($validated["search"]["start"], "00:00:00", true);
                $rentals->where("start", ">=", $start);
            }
            
            if(!empty($validated["search"]["end"]))
            {
                $end = Helper::setDateTime($validated["search"]["end"], "23:59:59", true);
                $rentals->where("end", "<=", $end);
            }
        }
        
        $total = $rentals->count();
        
        $orderBy = $this->getOrderBy($request, Rental::class, "full_number,desc");
        $rentals = $rentals->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        foreach($rentals as $i => $rental)
        {
            $rentals[$i]->tenant = $rental->getTenant();
            $rentals[$i]->item = $rental->getItem();
            $rentals[$i]->can_delete = $rental->canDelete();
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $rentals,
        ];
            
        return $out;
    }
    
    public function validateData(StoreRentalRequest $request)
    {
        $validated = $request->validated();
        Rental::checkDates($validated, $validated["item_id"] ?? null);
        return true;
    }
    
    public function rent(StoreRentRequest $request)
    {
        User::checkAccess("rent:create");
        
        $rentData = $request->all();
        
        $request->replace($rentData["item"]);
        $storeItemRequest = app(StoreItemRequest::class);
        $itemData = $storeItemRequest->validated();
        
        $request->replace($rentData["tenant"]);
        $storeTenantRequest = app(StoreTenantRequest::class);
        $tenantData = $storeTenantRequest->validated();
        
        $request->replace($rentData["rent"]);
        $storeRentalRequest = app(StoreRentalRequest::class);
        $rentData = $storeRentalRequest->validated();
        
        Rental::checkDates($rentData, $itemData["id"] ?? null);
        
        $rental = DB::transaction(function () use($itemData, $tenantData, $rentData) {
            if(empty($itemData["id"]))
                $itemData["id"] = $this->createItem($itemData);
            
            if(empty($tenantData["id"]))
                $tenantData["id"] = $this->createTenant($tenantData);
                
            $rental = new Rental;
            $rental->item_id = $itemData["id"];
            $rental->tenant_id = $tenantData["id"];
            $rental->document_date = $rentData["document_date"];
            $rental->start = Helper::setDateTime($rentData["start_date"], "00:00:00", true);
            $rental->period = $rentData["period"];
            $rental->months = $rentData["months"] ?? null;
            $rental->end = $rentData["period"] == Rental::PERIOD_DATE ? Helper::setDateTime($rentData["end_date"], "23:59:59", true) : null;
            $rental->termination_period = $rentData["termination_period"];
            $rental->termination_months = $rentData["termination_months"] ?? null;
            $rental->termination_days = $rentData["termination_days"] ?? null;
            $rental->deposit = $rentData["deposit"] ?? null;
            $rental->payment = $rentData["payment"];
            $rental->rent = $rentData["rent"];
            $rental->first_month_different_amount = $rentData["first_month_different_amount_value"] ?? null;
            $rental->last_month_different_amount = $rentData["last_month_different_amount_value"] ?? null;
            $rental->payment_day = $rentData["payment_day"] ?? null;
            $rental->first_payment_date = Helper::setDateTime($rentData["first_payment_date"], "23:59:59", true);
            $rental->number_of_people = $rentData["number_of_people"];
            $rental->comments = $rentData["comments"] ?? null;
            $rental->setEndDate();
            $rental->save();
            
            $rental->setCurrentRentalImmediately();
            $rental->generateInitialItemBills();
            
            return $rental;
        });
        
        return $rental->id;
    }
    
    public function get(Request $request, int $rentalId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $rental->tenant = $rental->getTenant();
        $rental->item = $rental->getItem();
        return $rental;
    }
    
    private function createItem($data)
    {
        $item = new Item;
        $item->type = $data["type"];
        $item->name = $data["name"];
        $item->street = $data["street"];
        $item->house_no = $data["house_no"] ?? null;
        $item->apartment_no = $data["apartment_no"] ?? null;
        $item->city = $data["city"];
        $item->zip = $data["zip"];
        $item->country = $data["country"] ?? null;
        $item->area = $data["area"] ?? null;
        $item->ownership_type = $data["ownership_type"];
        $item->customer_id = $data["ownership_type"] == Item::OWNERSHIP_MANAGE ? ($data["customer_id"] ?? null) : null;
        $item->room_rental = $data["room_rental"] ?? 0;
        $item->num_rooms = $data["num_rooms"] ?? null;
        $item->description = $data["description"] ?? null;
        $item->default_rent = $data["default_rent"] ?? null;
        $item->default_deposit = $data["default_deposit"] ?? null;
        $item->comments = $data["comments"] ?? null;
        $item->save();
        
        return $item->id;
    }
    
    private function createTenant($data)
    {
        $tenant = new Customer;
        $tenant->role = Customer::ROLE_TENANT;
        $tenant->type = $data["type"];
        $tenant->name = $data["name"];
        $tenant->street = $data["street"] ?? null;
        $tenant->house_no = $data["house_no"] ?? null;
        $tenant->apartment_no = $data["apartment_no"] ?? null;
        $tenant->city = $data["city"] ?? null;
        $tenant->zip = $data["zip"] ?? null;
        $tenant->country = $data["country"] ?? null;
        $tenant->nip = $data["nip"] ?? null;
        $tenant->regon = $data["regon"] ?? null;
        $tenant->pesel = $data["pesel"] ?? null;
        $tenant->document_type = $data["document_type"] ?? null;
        $tenant->document_number = $data["document_number"] ?? null;
        $tenant->document_extra = $data["document_extra"] ?? null;
        $tenant->comments = $data["comments"] ?? null;
        $tenant->send_sms = $data["send_sms"] ?? 0;
        $tenant->send_email = $data["send_email"] ?? 0;
        $tenant->save();
        
        if(!empty($data["contacts"]))
            $tenant->updateContact($data["contacts"]);
            
        return $tenant->id;
    }
    
    public function delete(Request $request, int $rentalId)
    {
        User::checkAccess("rent:delete");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $rental->delete();
        return true;
    }
    
    public function termination(TerminationRequest $request, int $rentalId)
    {
        User::checkAccess("rent:update");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();
        
        $rental->terminate(Helper::setDateTime($validated["end_date"], "23:59:59", true), $validated["termination_reason"] ?? "");
        
        return true;
    }
    
    public function bills(RentalBillRequest $request, int $rentalId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $rentalBills = ItemBill
            ::where("rental_id", $rentalId);
        
        $total = $rentalBills->count();
        
        $rentalBills = $rentalBills->take($size)
            ->skip(($page-1)*$size)
            ->orderBy("paid", "ASC")
            ->orderByRaw("CASE WHEN paid = 1 THEN payment_date ELSE -payment_date END DESC")
            ->get();
            
        foreach($rentalBills as $i => $itemBill)
        {
            $rentalBills[$i]->can_delete = $itemBill->canDelete();
            $rentalBills[$i]->bill_type = $itemBill->getBillType();
            $rentalBills[$i]->out_off_date = $itemBill->isOutOfDate();
        }
            
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $rentalBills,
        ];
            
        return $out;
    }
}