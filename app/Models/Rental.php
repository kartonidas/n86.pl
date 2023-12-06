<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use SoftDeletes;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    const PERIOD_MONTH = "month";
    const PERIOD_INDETERMINATE = "indeterminate";
    const PERIOD_DATE = "date";
    const PERIOD_TERM_MONTHS = "months";
    const PERIOD_TERM_DAYS = "days";
    const PAYMENT_CYCLICAL = "cyclical";
    const PAYMENT_ONETIME = "onetime";
    
    public static function getPeriods()
    {
        return [
            self::PERIOD_MONTH => __("In months"),
            self::PERIOD_INDETERMINATE => __("Indeterminate"),
            self::PERIOD_DATE => __("Specific date"),
        ];
    }
    
    public static function getTerminationPeriods()
    {
        return [
            self::PERIOD_TERM_MONTHS => __("Counted in months"),
            self::PERIOD_TERM_DAYS => __("Counted in days"),
        ];
    }
    
    public static function getPaymentsType()
    {
        return [
            self::PAYMENT_CYCLICAL => __("Cyclical"),
            self::PAYMENT_ONETIME => __("One time"),
        ];
    }
    
    public static function getPaymentDays()
    {
        $days = [];
        for($i = 1; $i <= 25; $i++)
            $days[$i] = $i;
        return $days;
    }
}