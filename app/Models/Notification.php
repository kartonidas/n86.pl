<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Mail\Subscription\Activated;
use App\Mail\Subscription\Expiration;
use App\Mail\Subscription\Expired;
use App\Mail\Subscription\Extend;
use App\Mail\Subscription\Renewed;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use App\Traits\DbTimestamp;

class Notification extends Model
{
    use DbTimestamp, SoftDeletes;
    
    public static function notify($user_id, $added_user_id, $object_id, $type, $extra_object_id = null)
    {
        $row = new self;
        $row->user_id = $user_id;
        $row->added_user_id = $added_user_id;
        $row->object_id = $object_id;
        $row->type = $type;
        $row->object_name = $row->getObjectName();
        $row->extra_object_id = $extra_object_id;
        $row->save();
        
        $user = User::find($row->user_id);
        $addedUser = User::find($row->added_user_id);
        if($user)
        {
            $settings = $user->getAccountSettings();
            $locale = $settings->locale;
            switch($type)
            {
                case "subscription:activated":
                case "subscription:renewed":
                case "subscription:extend":
                    $order = Order::withoutGlobalScopes()->find($row->object_id);
                    if($order)
                    {
                        $subscription = Subscription::withoutGlobalScopes()->find($order->subscription_id);
                        if($subscription)
                        {
                            if($type == "subscription:activated")
                                Mail::to($user->email)->locale($locale)->queue(new Activated($order, $subscription));
                            if($type == "subscription:renewed")
                                Mail::to($user->email)->locale($locale)->queue(new Renewed($order, $subscription));
                            if($type == "subscription:extend")
                                Mail::to($user->email)->locale($locale)->queue(new Extend($order, $subscription));
                                
                            $message = self::generateMessage($locale, $type, $subscription);
                            if($message)
                            {
                                $row->message = $message;
                                $row->save();
                            }
                        }
                    }
                break;
            
                case "subscription:expiration3":
                case "subscription:expired":
                    $subscription = Subscription::withoutGlobalScopes()->find($row->object_id);
                    if($subscription)
                    {
                        if($type == "subscription:expired")
                            Mail::to($user->email)->locale($locale)->queue(new Expired($subscription));
                        if($type == "subscription:expiration3")    
                            Mail::to($user->email)->locale($locale)->queue(new Expiration($subscription, 3));
                            
                        $message = self::generateMessage($locale, $type, $subscription);
                        if($message)
                        {
                            $row->message = $message;
                            $row->save();
                        }
                    }
                break;
            }
        }
    }
    
    public function getObjectName()
    {
        $out = [];
        switch($this->type)
        {
            case "invoice:generated":
                $invoice = Invoice::withoutGlobalScopes()->find($this->object_id);
                if($invoice)
                    return $invoice->full_number;
            break;
        }
        return "";
    }
    
    private static function generateMessage($locale, $type, $object)
    {
        if(in_array($type, ["subscription:expired", "subscription:expiration3", "subscription:activated", "subscription:renewed"]) && $object instanceof Subscription)
        {
            $view = "messages." . $locale . "." . $type;
            if(!view()->exists($view))
                $view = "messages." . config("api.default_language") . "." . $type;
            
            if(view()->exists($view))
                return View::make($view, ["row" => $object])->render();
        }
        return null;
    }
}