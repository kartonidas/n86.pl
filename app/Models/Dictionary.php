<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    public static function getAllowedTypes()
    {
        return [
            "fees" => __("Fee included in the rent"),
            "bills" => __("Type of bill")
        ];
    }
    
    public function scopeApiFields(Builder $query): void
    {
        $query->select("id", "type", "active", "name", "val");
    }
}