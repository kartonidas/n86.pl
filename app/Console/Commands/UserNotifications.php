<?php

namespace App\Console\Commands;

use Illuminate\Database\Eloquent\Collection;

use DateTime;
use DateInterval;
use Exception;
use Throwable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserNotification\ItemBillsGroup;
use App\Mail\UserNotification\ItemBillsGroupObject;
use App\Mail\UserNotification\ItemBillsSingle;
use App\Mail\UserNotification\RentalComingGroup;
use App\Mail\UserNotification\RentalComingSingle;
use App\Mail\UserNotification\RentalEndingGroup;
use App\Mail\UserNotification\RentalEndingSingle;
use App\Models\ConfigNotification;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\Rental;
use App\Models\User;
use App\Models\SendingNotificationObject;

class UserNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification user (user configured notifications)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $date = new DateTime();
        
        $configuredNotifications = ConfigNotification
            ::withoutGlobalScopes()
            ->user()
            ->where(function($q) use($date) {
                $q
                    ->whereNull("last_check")
                    ->orWhereDate("last_check", "<", $date->format("Y-m-d"));
            })
            ->get();
            
        foreach($configuredNotifications as $notification)
        {
            try {
                switch($notification->type)
                {
                    case ConfigNotification::TYPE_UNPAID_BILLS:
                        $this->sendUnpaidBills($notification, $date);
                    break;
                
                    case ConfigNotification::TYPE_RENTAL_ENDING:
                        $this->sendRentalEnding($notification, $date);
                    break;
                
                    case ConfigNotification::TYPE_RENTAL_COMING:
                        $this->sendRentalComing($notification, $date);
                    break;
                }
                
                $notification->last_check = $date->format("Y-m-d");
                $notification->save();
            } catch(Throwable $e) {
                echo $e->getMessage();
            }
        }
    }
    
    private function sendUnpaidBills(ConfigNotification $notification, DateTime $date)
    {
        if($notification->type != ConfigNotification::TYPE_UNPAID_BILLS)
            return;
        
        $user = User::find($notification->owner_id);
        if(!$user || $user->deleted)
            return;
        
        $ignoreObjectIds = SendingNotificationObject
            ::where("config_notification_id", $notification->id)
            ->where("date", $date->format("Y-m-d"))
            ->pluck("object_id")
            ->all();
            
        $ignoreItemIds = $this->getIgnoreItemIds($notification);
        $paymentDate = (new DateTime())->add(new DateInterval("P" . $notification->days . "D"));
        
        $itemBills = ItemBill
            ::withoutGlobalScopes()
            ->where("uuid", $notification->uuid)
            ->where("paid", 0)
            ->whereNotIn("item_id", $ignoreItemIds)
            ->whereNotIn("id", $ignoreObjectIds)
            ->where("payment_date", ">", $date->getTimestamp())
            ->where("payment_date", ">=", $paymentDate->setTime(0, 0, 0)->getTimestamp())
            ->where("payment_date", "<=", $paymentDate->setTime(23, 59, 59)->getTimestamp())
            ->orderBy("payment_date", "ASC")
            ->get();
        
        $groupedBills = $this->groupObjectBills($itemBills, $notification->mode);
        if(empty($groupedBills))
            return;
        
        switch($notification->mode)
        {
            case ConfigNotification::MODE_SINGLE:
                foreach($groupedBills as $bill)
                    Mail::to($user->email, $user->email)->locale($user->default_locale)->queue(new ItemBillsSingle($bill, $notification, $paymentDate));
            break;
                
            case ConfigNotification::MODE_GROUP:
                Mail::to($user->email, $user->email)->locale($user->default_locale)->queue(new ItemBillsGroup($groupedBills, $notification, $paymentDate));
            break;
                
            case ConfigNotification::MODE_GROUP_OBJECT:
                foreach($groupedBills as $bills)
                    Mail::to($user->email, $user->email)->locale($user->default_locale)->queue(new ItemBillsGroupObject($bills, $notification, $paymentDate));
            break;
        }
        
        foreach($itemBills as $bill)
        {
            $log = new SendingNotificationObject;
            $log->config_notification_id = $notification->id;
            $log->date = $date->format("Y-m-d");
            $log->object_id = $bill->id;
            $log->save();
        }
    }
    
    private function sendRentalEnding(ConfigNotification $notification, DateTime $date)
    {
        if($notification->type != ConfigNotification::TYPE_RENTAL_ENDING)
            return;
        
        $user = User::find($notification->owner_id);
        if(!$user || $user->deleted)
            return;
        
        $ignoreObjectIds = SendingNotificationObject
            ::where("config_notification_id", $notification->id)
            ->where("date", $date->format("Y-m-d"))
            ->pluck("object_id")
            ->all();
            
        $endDate = (new DateTime())->add(new DateInterval("P" . $notification->days . "D"));
        
        $endingRentals = Rental
            ::withoutGlobalScopes()
            ->where("uuid", $notification->uuid)
            ->whereNotIn("id", $ignoreObjectIds)
            ->where("end", ">", $date->getTimestamp())
            ->where(function($q) use($endDate) {
                $q
                    ->where(function($q2) use($endDate) {
                        $q2
                            ->where("end", ">=", $endDate->setTime(0, 0, 0)->getTimestamp())
                            ->where("end", "<=", $endDate->setTime(23, 59, 59)->getTimestamp());
                    })
                    ->orWhere(function($q2) use($endDate) {
                        $q2
                            ->where("termination", 1)
                            ->where("termination_time", ">=", $endDate->setTime(0, 0, 0)->getTimestamp())
                            ->where("termination_time", "<=", $endDate->setTime(23, 59, 59)->getTimestamp());
                    });
            })
            ->orderByRaw("CASE WHEN termination THEN termination_time ELSE end END ASC")
            ->get();
            
        $groupedRentals = $this->groupObjectRentals($endingRentals, $notification->mode);
        if(empty($groupedRentals))
            return;
        
        switch($notification->mode)
        {
            case ConfigNotification::MODE_SINGLE:
                foreach($groupedRentals as $rental)
                    Mail::to($user->email, $user->email)->locale($user->default_locale)->queue(new RentalEndingSingle($rental, $notification, $endDate));
            break;
            case ConfigNotification::MODE_GROUP:
                Mail::to($user->email, $user->email)->locale($user->default_locale)->queue(new RentalEndingGroup($groupedRentals, $notification, $endDate));
            break;
        }
        
        foreach($endingRentals as $rental)
        {
            $log = new SendingNotificationObject;
            $log->config_notification_id = $notification->id;
            $log->date = $date->format("Y-m-d");
            $log->object_id = $rental->id;
            $log->save();
        }
    }
    
    private function sendRentalComing(ConfigNotification $notification, DateTime $date)
    {
        if($notification->type != ConfigNotification::TYPE_RENTAL_COMING)
            return;
        
        $user = User::find($notification->owner_id);
        if(!$user || $user->deleted)
            return;
        
        $ignoreObjectIds = SendingNotificationObject
            ::where("config_notification_id", $notification->id)
            ->where("date", $date->format("Y-m-d"))
            ->pluck("object_id")
            ->all();
            
        $ignoreItemIds = $this->getIgnoreItemIds($notification);
        $comingDate = (new DateTime())->add(new DateInterval("P" . $notification->days . "D"));
        
        $comingRentals = Rental
            ::withoutGlobalScopes()
            ->where("uuid", $notification->uuid)
            ->where("status", Rental::STATUS_WAITING)
            ->whereNotIn("item_id", $ignoreItemIds)
            ->whereNotIn("id", $ignoreObjectIds)
            ->where("start", ">=", $comingDate->setTime(0, 0, 0)->getTimestamp())
            ->where("start", "<=", $comingDate->setTime(23, 59, 59)->getTimestamp())
            ->orderBy("start", "ASC")
            ->get();
            
        
        $groupedRentals = $this->groupObjectRentals($comingRentals, $notification->mode);
        if(empty($groupedRentals))
            return;
        
        switch($notification->mode)
        {
            case ConfigNotification::MODE_SINGLE:
                foreach($groupedRentals as $rental)
                    Mail::to($user->email, $user->email)->locale($user->default_locale)->queue(new RentalComingSingle($rental, $notification, $comingDate));
            break;
            case ConfigNotification::MODE_GROUP:
                Mail::to($user->email, $user->email)->locale($user->default_locale)->queue(new RentalComingGroup($groupedRentals, $notification, $comingDate));
            break;
        }
        
        foreach($comingRentals as $rental)
        {
            $log = new SendingNotificationObject;
            $log->config_notification_id = $notification->id;
            $log->date = $date->format("Y-m-d");
            $log->object_id = $rental->id;
            $log->save();
        }
    }
    
    private function getIgnoreItemIds(ConfigNotification $notification)
    {
        return Item
            ::withoutGlobalScopes()
            ->where("uuid", $notification->uuid)
            ->where(function($q){
                $q
                    ->whereNotNull("deleted_at")
                    ->orWhere("mode", Item::MODE_ARCHIVED);
            })
            ->pluck("id")
            ->all();
    }
    
    private function groupObjectBills(Collection $rows, $mode)
    {
        $out = [];
        $items = [];
        foreach($rows as $row)
        {
            if(!($row instanceof ItemBill))
                return [];
            
            $row->prepareViewData();
            $date = $row->payment_date;
            
            $billTypeName = $row->getBillType();
            $billTypeName = is_object($billTypeName) ? $billTypeName->toArray() : $billTypeName;
            
            $row->bill_type = $billTypeName ? $billTypeName["name"] : "";
            
            if(!isset($items[$row->item_id]))
                $items[$row->item_id] = Item::withoutGlobalScopes()->where("uuid", $row->uuid)->where("id", $row->item_id)->first();
            if(empty($items[$row->item_id]))
                continue;
            
            switch($mode)
            {
                case ConfigNotification::MODE_GROUP_OBJECT:
                case ConfigNotification::MODE_GROUP:
                    if(!isset($out[$row->item_id]))
                    {
                        $out[$row->item_id] = [];
                        $out[$row->item_id]["item"] = $items[$row->item_id]->toArray();
                        $out[$row->item_id]["bills"] = [];
                    }
                        
                    $out[$row->item_id]["bills"][] = $row->toArray();
                break;
            
                case ConfigNotification::MODE_SINGLE:
                    $out[] = [
                        "item" => $items[$row->item_id]->toArray(),
                        "bill" => $row->toArray(),
                    ];
                break;
            }
        }
        
        return $out;
    }
    
    private function groupObjectRentals(Collection $rows, $mode)
    {
        $out = [];
        $items = [];
        foreach($rows as $row)
        {
            if(!($row instanceof Rental))
                return [];
            
            $row->prepareViewData();
            $date = $row->termination ? $row->termination_time : $row->end;
            
            $row->end_date = $date;
            
            if(!isset($items[$row->item_id]))
                $items[$row->item_id] = Item::withoutGlobalScopes()->where("uuid", $row->uuid)->where("id", $row->item_id)->first();
            if(empty($items[$row->item_id]))
                continue;
            
            switch($mode)
            {
                case ConfigNotification::MODE_GROUP:
                    if(!isset($out[$row->item_id]))
                    {
                        $out[$row->item_id] = [];
                        $out[$row->item_id]["item"] = $items[$row->item_id]->toArray();
                        $out[$row->item_id]["rental"] = [];
                    }
                        
                    $out[$row->item_id]["rental"] = $row->toArray();
                break;
            
                case ConfigNotification::MODE_SINGLE:
                    $out[] = [
                        "item" => $items[$row->item_id]->toArray(),
                        "rental" => $row->toArray(),
                    ];
                break;
            }
        }
        
        return $out;
    }
}
