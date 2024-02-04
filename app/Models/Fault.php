<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Dictionary;
use App\Models\Item;

class Fault extends Model
{
    use SoftDeletes;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $hidden = ["uuid"];
    
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