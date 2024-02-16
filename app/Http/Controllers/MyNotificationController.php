<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Exceptions\ObjectNotExist;
use App\Http\Requests\MyNotificationRequest;
use App\Http\Requests\StoreMyNotificationRequest;
use App\Http\Requests\UpdateMyNotificationRequest;
use App\Models\ConfigNotification;

class MyNotificationController extends Controller
{
    public function list(MyNotificationRequest $request)
    {
        $validated = $request->validated();
        
        $size = $validated["size"] ?? config("api.list.size");
        $skip = isset($validated["first"]) ? $validated["first"] : (($validated["page"] ?? 1)-1)*$size;
        
        $confgNotifications = ConfigNotification::user()->where("owner_id", Auth::user()->id);
            
        $total = $confgNotifications->count();
        
        $confgNotifications = $confgNotifications->take($size)
            ->skip($skip)
            ->orderBy("id", "ASC")
            ->get();
        
        $out = [
            "total_rows" => $total,
            "total_pages" => ceil($total / $size),
            "data" => $confgNotifications,
        ];
            
        return $out;
    }
    
    public function create(StoreMyNotificationRequest $request)
    {
        $validated = $request->validated();
        
        $notification = new ConfigNotification;
        $notification->owner = ConfigNotification::OWNER_USER;
        $notification->owner_id = Auth::user()->id;
        $notification->type = $validated["type"];
        $notification->days = $validated["days"] ?? null;
        $notification->mode = $validated["mode"];
        $notification->save();
        
        return $notification->id;
    }
    
    public function get(Request $request, $notificationId)
    {
        $notification = ConfigNotification::where("id", $notificationId)->user()->where("owner_id", Auth::user()->id)->first();
        if(!$notification)
            throw new ObjectNotExist(__("Notification does not exist"));
        
        return $notification;
    }
    
    public function update(UpdateMyNotificationRequest $request, $notificationId)
    {
        $notification = ConfigNotification::where("id", $notificationId)->user()->where("owner_id", Auth::user()->id)->first();
        if(!$notification)
            throw new ObjectNotExist(__("Notification does not exist"));
        
        $validated = $request->validated();
        
        foreach($validated as $field => $value)
            $notification->{$field} = $value;
        $notification->save();
        
        return true;
    }
    
    public function delete(Request $request, $notificationId)
    {
        $notification = ConfigNotification::where("id", $notificationId)->user()->where("owner_id", Auth::user()->id)->first();
        if(!$notification)
            throw new ObjectNotExist(__("Notification does not exist"));
        
        $notification->delete();
        return true;
    }
}