<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;

class ConfigNotification extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    const OWNER_USER = "user";
    const OWNER_TENANT = "tenant";
    const OWNER_CUSTOMER = "customer";
    const TYPE_UNPAID_BILLS = "unpaid_bills";
    const TYPE_RENTAL_ENDING = "rental_ending";
    const TYPE_RENTAL_ENDED = "rental_ended";
    const MODE_SINGLE = "single";
    const MODE_GROUP = "group";
    const MODE_GROUP_OBJECT = "group_object";
    
    public static function getAllowedOwners()
    {
        return [
            self::OWNER_USER => __("User"),
            self::OWNER_TENANT => __("Tenant"),
            self::OWNER_CUSTOMER => __("Customer"),
        ];
    }
    
    public static function getAllowedTypes()
    {
        return [
            self::TYPE_UNPAID_BILLS => [
                "name" => __("Unpaid bills"),
                "days" => true,
                "allowed_days" => [1,2,3,4,5,6,7,8,9,10,11,12,13,14],
                "modes" => [self::MODE_SINGLE, self::MODE_GROUP, self::MODE_GROUP_OBJECT]
            ],
            self::TYPE_RENTAL_ENDING => [
                "name" => __("Rental ending"),
                "days" => true,
                "allowed_days" => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],
                "modes" => [self::MODE_SINGLE, self::MODE_GROUP]
            ],
            self::TYPE_RENTAL_ENDED => [
                "name" => __("Rental ended"),
                "days" => false,
                "modes" => [self::MODE_SINGLE]
            ],
        ];
    }
    
    public static function getAllowedModes()
    {
        return [
            self::MODE_SINGLE => __("Single"),
            self::MODE_GROUP => __("Group"),
            self::MODE_GROUP_OBJECT => __("Group in object"),
        ];
    }
    
    protected $casts = [
        "days" => "integer",
    ];
    protected $hidden = ["uuid"];
    
    public function scopeUser(Builder $query): void
    {
        $query->where("owner", self::OWNER_USER);
    }
    
    public function scopeCustomer(Builder $query): void
    {
        $query->where("owner", self::OWNER_CUSTOMER);
    }
    
    public function scopeTenant(Builder $query): void
    {
        $query->where("owner", self::OWNER_TENANT);
    }
}