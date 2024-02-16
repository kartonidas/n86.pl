<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Exceptions\Exception;
use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Http\Requests\FaultRequest;
use App\Http\Requests\StoreFaultRequest;
use App\Http\Requests\UpdateFaultRequest;
use App\Libraries\Helper;
use App\Models\Fault;
use App\Models\Item;
use App\Models\User;
use App\Traits\Sortable;

class FaultController extends Controller
{
    use Sortable;
    
    public function list(FaultRequest $request)
    {
        User::checkAccess("fault:list");
        
        $validated = $request->validated();

        $size = $validated["size"] ?? config("api.list.size");
        $skip = isset($validated["first"]) ? $validated["first"] : (($validated["page"] ?? 1)-1)*$size;
        
        $faults = Fault::whereRaw("1=1");
        
        if(!empty($validated["search"]))
        {
            if(!empty($validated["search"]["item_name"]) || !empty($validated["search"]["item_address"]))
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
                
                $itemIds = $items->pluck("id")->all();
                $faults->whereIn("item_id", !empty($itemIds) ? $itemIds : [-1]);
            }
            
            if(!empty($validated["search"]["status"]))
                $faults->where("status_id", $validated["search"]["status"]);
                
            if(!empty($validated["search"]["priority"]))
                $faults->where("priority", $validated["search"]["priority"]);
            
            if(!empty($validated["search"]["start"]))
                $faults->whereDate("created_at", ">=", $validated["search"]["start"]);
            
            if(!empty($validated["search"]["end"]))
                $faults->whereWate("created_at", "<=", $validated["search"]["end"]);
        }
        
        $total = $faults->count();
        
        $orderBy = $this->getOrderBy($request, Fault::class, "created_at,desc");
        $faults = $faults->take($size)
            ->skip($skip)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
        
        
        foreach($faults as $i => $fault)
        {
            $faults[$i]->item = $fault->getItem();
            $faults[$i]->status = $fault->getStatus();
            $faults[$i]->prepareViewData();
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "data" => $faults,
        ];
            
        return $out;
    }
    
    public function create(StoreFaultRequest $request)
    {
        User::checkAccess("fault:create");
        
        $validated = $request->validated();
        
        $fault = new Fault;
        $fault->status_id = $validated["status_id"];
        $fault->item_id = $validated["item_id"];
        $fault->priority = $validated["priority"];
        $fault->description = $validated["description"];
        $fault->created_user_id = Auth::user()->id;
        $fault->save();
        
        return $fault->id;
    }
    
    public function get(Request $request, int $faultId)
    {
        User::checkAccess("fault:list");
        
        $fault = Fault::find($faultId);
        if(!$fault)
            throw new ObjectNotExist(__("Fault does not exist"));
        
        $fault->item = $fault->getItem();
        $fault->status = $fault->getStatus();
        $fault->prepareViewData();
        
        return $fault;
    }
    
    public function update(UpdateFaultRequest $request, int $faultId)
    {
        User::checkAccess("fault:update");
        
        $fault = Fault::find($faultId);
        if(!$fault)
            throw new ObjectNotExist(__("Fault does not exist"));
        
        $item = $fault->item()->first();
        if(!$item || !$item->canEdit())
            throw new InvalidStatus(__("Cannot update fault"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
            $fault->{$field} = $value;
        $fault->save();
        
        return true;
    }
    
    public function delete(Request $request, int $faultId)
    {
        User::checkAccess("fault:delete");
        
        $fault = Fault::find($faultId);
        if(!$fault)
            throw new ObjectNotExist(__("Fault does not exist"));
        
        $fault->delete();
        return true;
    }
}