<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Exception;
use App\Libraries\Data;
use App\Models\HistoryLog;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;

class History extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    const ACTION_CREATE = "create";
    const ACTION_UPDATE = "update";
    const ACTION_DELETE = "delete";
    
    protected $hidden = ["uuid"];
    protected $casts = [
        "created_at" => 'datetime:Y-m-d H:i:s',
        "updated_at" => 'datetime:Y-m-d H:i:s',
    ];

    protected $table = "history";
    private static $cachedData = [];
    
    public static function log(string $action, Model $object, $parentObject = null)
    {
        if(!in_array($action, [self::ACTION_CREATE, self::ACTION_UPDATE, self::ACTION_DELETE]))
            throw new Exception(__("Invalid action"));
        
        $log = "";
        switch($action)
        {
            case self::ACTION_UPDATE:
                $differences = self::getDifferences($object);
                
                if(empty($differences))
                    return;
                
                $log = serialize($differences);
            break;
        }
        
        $relatedObjects = self::getRelatedObjects($object);
        
        $uuid = null;
        if(!Auth::check())
        {
            if(!empty($object->uuid))
                $uuid = $object->uuid;
            
            foreach($relatedObjects as $relatedObject)
            {
                if(!empty($relatedObject->uuid))
                {
                    $uuid = $relatedObject->uuid;
                    break;
                }
            }
            
            if(!$uuid)
                return;
        }
        
        self::logHistory(
            $action,
            $object::class,
            $object->id,
            $log,
            "",
            $relatedObjects,
            $uuid
        );
    }
    
    private static function getDifferences(Model $object)
    {
        $ignore = ["created_at", "updated_at", "deleted_at"];
        $ignore = array_merge($ignore, self::getIgnoreFields($object));
        
        $changes = $object->getChanges();
        $original = $object->getRawOriginal();

        $changeHistory = [];
        if(!empty($changes))
        {
            foreach($changes as $field => $value)
            {
                if(in_array($field, $ignore))
                    continue;

                $changeHistory[$field] = [$original[$field] ?? "", $value];
            }
        }
        
        return $changeHistory;
    }
    
    private static function getRelatedObjects(Model $object) : array
    {
        $relatedObjects = [];
        switch($object::class)
        {
            case "App\Models\Rental":
                $item = Item::withTrashed()->find($object->item_id);
                if($item)
                    $relatedObjects[] = $item;
            break;
        
            case "App\Models\ItemBill":
                if($object->rental_id)
                {
                    $rental = Rental::withTrashed()->find($object->rental_id);
                    if($rental)
                        $relatedObjects[] = $rental;
                }
                $item = Item::withTrashed()->find($object->item_id);
                if($item)
                    $relatedObjects[] = $item;
            break;
        
            case "App\Models\ItemCyclicalFee":
                $item = Item::withTrashed()->find($object->item_id);
                if($item)
                    $relatedObjects[] = $item;
            break;
        }
        return $relatedObjects;
    }
    
    private static function getIgnoreFields(Model $object)
    {
        $ignore = [];
        switch($object::class)
        {
            case "App\Models\Item":
                $ignore[] = "balance";
                $ignore[] = "waiting_rentals";
                $ignore[] = "room_rental";
            break;
            case "App\Models\Rental":
                $ignore[] = "balance";
                $ignore[] = "next_rental";
            break;
        }
        return $ignore;
    }
    
    private static function logHistory($event, $objectType, $objectId, $log = "", $description = "", $relatedObjects = null, $uuid = null)
    {
        if(strpos($event, ":") !== false)
            list($event, $subevent) = explode(":", $event, 2);
            
        if(!Auth::check() && empty($uuid))
            return;

        $historyLog = new HistoryLog;
        $historyLog->object_type = $objectType;
        $historyLog->object_id = $objectId;
        $historyLog->log = $log;
        $historyLog->description = $description;
        $historyLog->save();
        
        $history = new History;
        if(!Auth::check())
            $history->uuid = $uuid;
        $history->event = $event;
        $history->object_type = $historyLog->object_type;
        $history->object_id = $historyLog->object_id;
        $history->user_id = Auth::check() ? Auth::user()->id : 0;
        $history->history_log_id = $historyLog->id;
        $history->save();
        
        foreach($relatedObjects as $related)
        {
            $historyRelated = new History;
            $historyRelated->uuid = $history->uuid;
            $historyRelated->event = $history->event;
            $historyRelated->object_type = $related::class;
            $historyRelated->object_id = $related->id;
            $historyRelated->user_id = $history->user_id;
            $historyRelated->history_log_id = $history->history_log_id;
            $historyRelated->save();
        }
    }

    public function getEvent()
    {
        switch($this->event)
        {
            case "update":
                return __("Record updated");
            case "create":
                return __("Record created");
            case "delete":
                return __("Record deleted");
        }
    }

    public function getUser()
    {
        if(empty($this->user_id))
            return "SYSTEM";
        
        if(empty(self::$cachedData["user"][$this->user_id]))
            self::$cachedData["user"][$this->user_id] = User::withTrashed()->find($this->user_id);

        if(empty(self::$cachedData["user"][$this->user_id]))
        {
            self::$cachedData["user"][$this->user_id] = "Użytkownik usuniety";
            return self::$cachedData["user"][$this->user_id];
        }

        return (self::$cachedData["user"][$this->user_id]->firstname ?? "-") . " " . (self::$cachedData["user"][$this->user_id]->lastname ?? "");
    }

    public function prepareDiffLog()
    {
        if($this->event != self::ACTION_UPDATE)
            return null;

        $historyLog = $this->historyLog()->first();
        if(!$historyLog)
            return null;
            
        $out = [];
        $log = unserialize($historyLog->log);
        foreach($log as $field => $value)
        {
            $configValue = Data::getHistoryFields($historyLog->object_type);
            if(empty($configValue[$field]))
                continue;

            $configValue = $configValue[$field];

            $old = $value[0];
            $new = $value[1];
            if(!empty($configValue[1]))
            {
                $old = self::getFieldValue($configValue[1], $old);
                $new = self::getFieldValue($configValue[1], $new);
            }

            $out[] = [
                "field" => $configValue[0],
                "old" => $old,
                "new" => $new,
            ];
        }
        return $out;
    }

    public function renderLog()
    {
        if($this->event == self::ACTION_UPDATE)
        {
            $diff = $this->prepareDiffLog();
            return view("panel.partials.history-diff", ["diff" => $diff])->render();
        }
        return "";
    }

    private static function getFieldValue($class, $value)
    {
        
    }
    
    public function historyLog(): BelongsTo
    {
        return $this->belongsTo(HistoryLog::class);
    }

}
