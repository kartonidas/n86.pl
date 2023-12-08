<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\StoreRentRequest;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Libraries\Helper;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;
use App\Traits\Sortable;
use App\Models\User;

class RentalController extends Controller
{
    use Sortable;
    
    public function list(Request $request)
    {
        User::checkAccess("rent:list");
        
        $validated = $request->validate([
            "size" => "nullable|integer|gt:0",
            "page" => "nullable|integer|gt:0",
            "sort" => "nullable",
            "order" => "nullable|integer",
            "search.item_id" => "sometimes|integer",
            "search.tenant_id" => "sometimes|integer",
            "search.status" => "sometimes|string",
        ]);
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $rentals = Rental
            ::apiFields();
        
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["item_id"]))
                $rentals->where("item_id", $validated["search"]["item_id"]);
            if(!empty($validated["search"]["tenant_id"]))
                $rentals->where("tenant_id", $validated["search"]["tenant_id"]);
            if(!empty($validated["search"]["status"]))
                $rentals->where("status", $validated["search"]["status"]);
        }
        
        $total = $rentals->count();
        
        $orderBy = $this->getOrderBy($request, Rental::class, "start,desc");
        $rentals = $rentals->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        foreach($rentals as $i => $rental)
        {
            $rentals[$i]->tenant = $rental->getTenant();
            $rentals[$i]->item = $rental->getItem();
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
            $rental->start = strtotime(Helper::setDateTime($rentData["start_date"], "00:00:00"));
            $rental->period = $rentData["period"];
            $rental->months = $rentData["months"] ?? null;
            $rental->end = $rentData["period"] == Rental::PERIOD_DATE ? strtotime(Helper::setDateTime($rentData["end_date"], "23:59:59")) : null;
            $rental->termination_period = $rentData["termination_period"];
            $rental->termination_months = $rentData["termination_months"] ?? null;
            $rental->termination_days = $rentData["termination_days"] ?? null;
            $rental->deposit = $rentData["deposit"] ?? null;
            $rental->payment = $rentData["payment"];
            $rental->rent = $rentData["rent"];
            $rental->first_month_different_amount = $rentData["first_month_different_amount_value"] ?? null;
            $rental->last_month_different_amount = $rentData["last_month_different_amount_value"] ?? null;
            $rental->payment_day = $rentData["payment_day"] ?? null;
            $rental->first_payment_date = strtotime($rentData["first_payment_date"]);
            $rental->number_of_people = $rentData["number_of_people"];
            $rental->comments = $rentData["comments"] ?? null;
            $rental->setEndDate();
            $rental->save();
            
            return $rental;
        });
        
        return $rental->id;
    }
    
    public function get(Request $request, int $rentalId)
    {
        User::checkAccess("rent:list");
        
        $rental = Rental::apiFields()->find($rentalId);
        if(!$rental)
            throw new ObjectNotExist(__("Rental does not exist"));
        
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
}