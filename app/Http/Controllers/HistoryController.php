<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Exceptions\Exception;
use App\Http\Requests\HistoryRequest;
use App\Models\Customer;
use App\Models\History;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\ItemCyclicalFee;
use App\Models\Rental;
use App\Models\User;
use App\Traits\Sortable;

class HistoryController extends Controller
{
    use Sortable;
    
    public function list(HistoryRequest $request, string $object, int $objectId) {
        User::checkAccess("history:list");
        
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $page = $validated["page"] ?? 1;
        
        $history = History
            ::where("object_type", self::getObjectClassName($object))
            ->where("object_id", $objectId);
            
        $total = $history->count();
        
        $orderBy = $this->getOrderBy($request, History::class, "created_at,desc");
        $history = $history->take($size)
            ->skip(($page-1)*$size)
            ->orderBy($orderBy[0], $orderBy[1])
            ->get();
            
        $historyOut = [];
        foreach($history as $h)
        {
            $historyOut[] = [
                "event" => $h->getEvent(),
                "object_type" => $h->object_type,
                "user_id" => $h->user_id,
                "user" => $h->getUser(),
                "diff" => $h->prepareDiffLog(),
                "created_at" => $h->created_at,
            ];
        }
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "current_page" => $page,
            "has_more" => ceil($total / $size) > $page,
            "data" => $historyOut,
        ];
            
        return $out;
    }
    
    private static function getObjectClassName(string $object)
    {
        switch($object)
        {
            case "customer":
                return Customer::class;
            break;
        
            case "item":
                return Item::class;
            break;
            
            case "item_bill":
                return ItemBill::class;
            break;
            
            case "item_cyclical_fee":
                return ItemCyclicalFee::class;
            break;
            
            case "rental":
                return Rental::class;
            break;
        }
        
        throw new Exception(__("Unsupported object type"));
    }
    
    //private static function getRelatedObject(string $object)
}