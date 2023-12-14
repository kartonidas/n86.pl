<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ItemCyclicalFeeCost extends Model
{
    protected $casts = [
        "cost" => "float",
    ];
    
    protected function fromTime(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => date("Y-m-d", $value),
        );
    }
}