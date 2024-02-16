<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Item;
use App\Models\Rental;
use App\Models\User;

class Document extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    public static $sortable = ["title"];
    public static $defaultSortable = ["created_at", "desc"];
    
    protected $casts = [
        "created_at" => 'datetime:Y-m-d H:i',
        "updated_at" => 'datetime:Y-m-d H:i',
    ];
    
    protected $hidden = ["uuid"];
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}