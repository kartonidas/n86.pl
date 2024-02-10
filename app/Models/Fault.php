<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Models\Dictionary;
use App\Models\Item;

class Fault extends Model
{
    use SoftDeletes;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    const LEVEL_LOW = "low";
    const LEVEL_NORMAL = "normal";
    const LEVEL_HIGH = "high";
    
    protected $hidden = ["uuid"];
    
    public static $sortable = ["created_at"];
    public static $defaultSortable = ["created_at", "desc"];
    
    protected $casts = [
        "created_at" => "datetime:Y-m-d",
    ];
    
    public static function getPriorities()
    {
        return [
            self::LEVEL_LOW => __("Low priority"),
            self::LEVEL_NORMAL => __("Normal priority"),
            self::LEVEL_HIGH => __("High priority"),
        ];
    }
    
    protected function descriptionHtml(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => nl2br($attributes["description"]),
        );
    }
    
    protected function descriptionShort(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Str::limit($attributes["description"], 250),
        );
    }
    
    public function prepareViewData()
    {
        $this->description_html = $this->descriptionHtml;
        $this->description_short = $this->descriptionShort;
    }
    
    public function getItem()
    {
        return $this->item()->first();
    }
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->where("uuid", $this->uuid)->withoutGlobalScopes();
    }
    
    private static $cachedStatuses = [];
    public function getStatus()
    {
        if(!isset(self::$cachedStatuses[$this->status_id]))
            self::$cachedStatuses[$this->status_id] = Dictionary::find($this->status_id);
        
        return self::$cachedStatuses[$this->status_id];
    }
}