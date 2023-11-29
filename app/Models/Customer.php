<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    public const TYPE_CUSTOMER = "customer";
    public const TYPE_TENANT = "tenant";
    
    public static $sortable = ["name", "nip"];
    public static $defaultSortable = ["name", "asc"];
    
    public function scopeApiFields(Builder $query): void
    {
        $query->select("id", "name", "street", "house_no", "apartment_no", "city", "zip", "nip", "created_at");
    }
    
    public function scopeCustomer(Builder $query): void
    {
        $query->where("type", self::TYPE_CUSTOMER);
    }
    
    public function scopeTenant(Builder $query): void
    {
        $query->where("type", self::TYPE_TENANT);
    }
}