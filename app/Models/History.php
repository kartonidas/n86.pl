<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Exception;
use App\Models\User;

class History extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    const ACTION_CREATE = "create";
    const ACTION_UPDATE = "update";
    const ACTION_DELETE = "delete";

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
                if(!empty($differences))
                    $log = serialize($differences);
            break;
        }
        
        self::logHistory(
            $object::class,
            $action,
            $object->id,
            $log,
            "",
            $parentObject ? $parentObject::class : null,
            $parentObject ? $parentObject->id : null,
        );
    }
    
    private static function getDifferences(Model $object)
    {
        $ignore = ["created_at", "updated_at", "deleted_at"];
        
        $changes = $object->getChanges();
        $original = $object->getOriginal();

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
    
    private static function logHistory($type, $event, $objectId, $log = "", $description = "", $parentType = null, $parentId = null)
    {
        if(strpos($event, ":") !== false)
            list($event, $subevent) = explode(":", $event, 2);

        $row = new History;
        $row->event = $event;
        $row->object_type = $type;
        $row->object_id = $objectId;
        $row->user_id = Auth::user()->id;
        $row->log = $log;
        $row->description = $description;
        $row->parent_object_id = $parentId;
        $row->parent_object_type = $parentType;
        $row->save();
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
        if(empty(self::$cachedData["user"][$this->user_id]))
            self::$cachedData["user"][$this->user_id] = User::find($this->user_id);

        if(empty(self::$cachedData["user"][$this->user_id]))
        {
            self::$cachedData["user"][$this->user_id] = "Użytkownik usuniety";
            return self::$cachedData["user"][$this->user_id];
        }

        return (self::$cachedData["user"][$this->user_id]->firstname ?? "-") . " " . (self::$cachedData["user"][$this->user_id]->lastname ?? "");
    }

    private function prepareDiffLog()
    {
        if($this->event == self::ACTION_UPDATE)
            return "";

        $out = [];
        $log = unserialize($this->log);
        foreach($log as $field => $value)
        {
            $historyKey = "history." . $this->type . "." . $field;
            if(empty(config($historyKey)))
                continue;

            $configValue = config($historyKey);

            $old = $value[0];
            $new = $value[1];
            if(!empty($configValue[1]))
            {
                $old = self::getFieldValue($configValue[1], $old);
                $new = self::getFieldValue($configValue[1], $new);
            }

            $out[] = [
                "field" => $configValue[0],
                "nane" => mb_strtolower($configValue[0]),
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
}
