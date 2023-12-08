<?php

namespace App\Models;

use DateTime;
use DateInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exceptions\InvalidRentalDates;
use App\Exceptions\InvalidStatus;
use App\Libraries\Helper;

class Rental extends Model
{
    use SoftDeletes;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    const STATUS_ARCHIVE = "archive";
    const STATUS_CURRENT = "current";
    const STATUS_WAITING = "waiting";
    
    const PERIOD_MONTH = "month";
    const PERIOD_INDETERMINATE = "indeterminate";
    const PERIOD_DATE = "date";
    const PERIOD_TERM_MONTHS = "months";
    const PERIOD_TERM_DAYS = "days";
    const PAYMENT_CYCLICAL = "cyclical";
    const PAYMENT_ONETIME = "onetime";
    
    public static $sortable = ["start", "status", "end"];
    public static $defaultSortable = ["start", "desc"];
    
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
    
    public function scopeApiFields(Builder $query): void
    {
        $query->select(
            "id",
            "item_id",
            "tenant_id",
            "start",
            "period",
            "months",
            "end",
            "termination_period",
            "termination_months",
            "termination_days",
            "deposit",
            "payment",
            "rent",
            "first_month_different_amount",
            "last_month_different_amount",
            "payment_day",
            "first_payment_date",
            "number_of_people",
            "comments",
            "finished",
            "status",
            "created_at"
        );
    }
    
    public static function getPaymentDays()
    {
        $days = [];
        for($i = 1; $i <= 25; $i++)
            $days[$i] = $i;
        return $days;
    }
    
    public static function checkDates(array $rent, int $itemId = null)
    {
        if(empty($itemId))
            return;
        
        $startDate = Helper::setDateTime($rent["start_date"], "00:00:00", true);
        $endDate = null;
            
        switch($rent["period"])
        {
            case Rental::PERIOD_MONTH:
                $endDate = self::calculateEndDate(strtotime($rent["start_date"]), $rent["months"]);
            break;
                
            case Rental::PERIOD_DATE:
                $endDate = Helper::setDateTime($rent["end_date"], "23:59:59", true);
            break;
        }
        
        $statuses = [self::STATUS_CURRENT, self::STATUS_WAITING];
        if($startDate && $endDate)
        {
            $c1 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", "<=", $startDate)->where("end", ">=", $startDate)->count();
            $c2 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", "<=", $endDate)->where("end", ">=", $endDate)->count();
            $c3 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", "<=", $startDate)->whereNull("end")->count();
            $c4 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", "<=", $endDate)->whereNull("end")->count();
            
            if($c1 || $c2 || $c3 || $c4)
                throw new InvalidRentalDates(__("Cannot rented during the given time period"));
        }
        elseif($startDate)
        {
            // na czas nieokreślony nie moga istnieć żadne przyszłe rezerwacje!!!!!!
            $c1 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", "<=", $startDate)->where("end", ">=", $startDate)->count();
            $c2 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", ">=", $startDate)->count();
            $c3 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", "<=", $startDate)->whereNull("end")->count();
            
            if($c1 || $c2 || $c3)
                throw new InvalidRentalDates(__("Cannot rented during the given time period"));
        }
    }
    
    public function setEndDate()
    {
        if($this->period == self::PERIOD_MONTH)
            $this->end = self::calculateEndDate($this->start, $this->months);
    }
    
    public static function calculateEndDate(int $start, int $months)
    {
        $startDate = new DateTime();
        $startDate->setTimestamp($start);
        
        $startDate->add(new DateInterval("P" . $months . "M"));
        return Helper::setDateTime($startDate->format("Y-m-d"), "23:59:59", true);
    }
    
    public function getTenant()
    {
        return $this->tenant()->apiFields()->first();
    }
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function getItem()
    {
        return $this->item()->apiFields()->first();
    }
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    
    public static function checkWaitingRentals()
    {
        $time = time();
        $rentals = self
            ::where("status", self::STATUS_WAITING)
            ->where("start", ">=", $time)
            ->where(function($q) use($time) {
                $q->where("end", "<=", $time)->orWhereNull("end");
            })
            ->withoutGlobalScopes()
            ->get();
            
        foreach($rentals as $rental)
            $rental->setCurrent();
    }
    
    public static function checkCurrentRentals()
    {
        $time = time();
        $rentals = self
            ::where("status", self::STATUS_CURRENT)
            ->where("end", "<", $time)
            ->withoutGlobalScopes()
            ->get();
            
        foreach($rentals as $rental)
            $rental->setArchive();
    }
    
    private function setCurrent()
    {
        $this->status = self::STATUS_CURRENT;
        $this->saveQuietly();
        
        $item = $this->item()->withoutGlobalScopes()->first();
        if($item)
        {
            $item->rented = 1;
            $item->saveQuietly();
        }
    }
    
    private function setArchive()
    {
        $this->status = self::STATUS_ARCHIVE;
        $this->saveQuietly();
        
        $item = $this->item()->withoutGlobalScopes()->first();
        if($item)
        {
            $item->rented = 0;
            $item->saveQuietly();
        }
    }
    
    public function initStatus()
    {
        $time = time();
        if($this->start && $this->end)
        {
            if($time >= $this->start && $time <= $this->end)
                return self::STATUS_CURRENT;
            else
            {
                if($this->start > $time)
                    return self::STATUS_WAITING;
            }
        }
        elseif($this->start)
        {
            if($this->start > $time)
                return self::STATUS_WAITING;
            else
                return self::STATUS_CURRENT;
        }
    }
}