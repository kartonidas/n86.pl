<?php

namespace App\Http\Controllers;

use DateTime;
use DateInterval;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ObjectNotExist;
use App\Libraries\Helper;
use App\Models\Country;
use App\Models\FirmInvoicingData;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\Rental;
use App\Models\Subscription;
use App\Models\User;

class IndexController extends Controller
{
    /**
    * Get dashboard stats
    * @group dashboard
    */
    public function dashboard(Request $request)
    {
        $time = time();
        $time7d = (new DateTime)->add(new DateInterval("P7D"))->setTime(23, 59, 59)->getTimestamp();
        $time14d = (new DateTime)->add(new DateInterval("P7D"))->setTime(23, 59, 59)->getTimestamp();
        
        $unpaidBills = ItemBill::where("paid", 0)->where("payment_date", "<", $time)->orderBy("payment_date", "ASC")->limit(5)->get();
        foreach($unpaidBills as $i => $unpaidBill)
        {
            $unpaidBills[$i]->bill_type = $unpaidBill->getBillType();
            $unpaidBills[$i]->item = $unpaidBill->item()->first();
            $unpaidBills[$i]->prepareViewData();
        }
            
        $upcomingBills = ItemBill::where("paid", 0)->where("payment_date", ">=", time())->where("payment_date", "<", $time7d)->orderBy("payment_date", "ASC")->limit(5)->get();
        foreach($upcomingBills as $i => $upcomingBill)
        {
            $upcomingBills[$i]->bill_type = $upcomingBill->getBillType();
            $upcomingBills[$i]->item = $upcomingBill->item()->first();
            $upcomingBills[$i]->prepareViewData();
        }
        
        $rentals = Rental::active()->where("end", "<", $time14d)->get();
        $rentals = Rental::active()->where(function($query) use ($time14d) {
            $query->where("end", "<", $time14d)->orWhere("termination_time", $time14d);
        })->get();
        foreach($rentals as $i => $rental)
        {
            $rentals[$i]->prepareViewData();
            $rentals[$i]->tenant = $rental->getTenant();
            $rentals[$i]->item = $rental->getItem();
        }
        
        $out = [
            "total_items" => Item::active()->count(),
            "total_rentals" => Rental::active()->count(),
            "unpaid_bills" => $unpaidBills,
            "upcoming_bills" => $upcomingBills,
            "rentals" => $rentals,
        ];
        
        return $out;
    }
    
    /**
    * Get active subscription
    * @response 200 {"start": 1686580275, "start_date": "2023-01-02 10:00:12", "end": 1686580275, "end_date": "2023-01-02 11:00:12"}
    *
    * @group Subscription
    */
    public function getActiveSubscription()
    {
        $out = [];
        $subscription = Subscription::where("status", Subscription::STATUS_ACTIVE)->first();
        if($subscription)
        {
            $out = [
                "start" => $subscription->start,
                "start_date" => date("Y-m-d H:i:s", $subscription->start),
                "end" => $subscription->end,
                "end_date" => date("Y-m-d H:i:s", $subscription->end),
                "items" => $subscription->items,
                "days_to_end" => $subscription->calculateDaysToEnd(),
                "current" => [
                    "items" => Item::active()->count(),
                ]
            ];
        }
        return $out;
    }
    
    /**
    * Return package limits
    * @group Subscription
    */
    public function packages()
    {
        if(Auth::check())
        {
            $invoicingData = FirmInvoicingData
                ::where("uuid", Auth::user()->getUuid())
                ->invoice()
                ->whereNull("deleted_at")
                ->withoutGlobalScopes()
                ->first();
            
            if($invoicingData)
            {
                $foreign = strtolower($invoicingData->country) != "pl";
                $reverseCharge = $foreign && $invoicingData->type == "firm";
                    
                if($reverseCharge)
                {
                    $packages = config("packages");
                    foreach($packages["allowed"] as $k => $p)
                    {
                        $packages["allowed"][$k]["price"] = $p["price"] * ((100 + $p["vat"]) / 100);
                        $packages["allowed"][$k]["price_gross"] = $p["price"] * ((100 + $p["vat"]) / 100);
                    }
                    
                    $packages["reverse"] = true;
                    return $packages;
                }
            }
        }
        
        $packages = config("packages");
        foreach($packages["allowed"] as $k => $p)
            $packages["allowed"][$k]["price_gross"] = $p["price"] * ((100 + $p["vat"]) / 100);
        
        return $packages;
    }
    
    /**
    * Return countries list
    * @queryParam lang string Language Default: pl
    * @group Others
    */
    public function countries(Request $request)
    {
        $lang = $request->input("lang", "pl");
        
        return Country::select("code", ($lang == "pl" ? "name" : "name_en AS name"), "eu")
            ->orderBy("sort", "DESC")
            ->orderBy("eu", "DESC")
            ->orderBy(($lang == "pl" ? "name" : "name_en"), "DESC")
            ->get();
    }
}