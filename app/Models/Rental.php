<?php

namespace App\Models;

use DateTime;
use DateInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Exceptions\InvalidRentalDates;
use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Libraries\Data;
use App\Libraries\Helper;
use App\Models\Balances;
use App\Models\ItemBill;

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
    
    protected $casts = [
        "deposit" => "float",
        "rent" => "float",
        "first_month_different_amount" => "float",
        "last_month_different_amount" => "float",
    ];
    protected $hidden = ["uuid"];
    
    protected function start(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
    protected function end(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
    protected function firstPaymentDate(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
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
    
    public static function getStatuses()
    {
        return [
            self::STATUS_ARCHIVE => __("Archive"),
            self::STATUS_CURRENT => __("Current"),
            self::STATUS_WAITING => __("Waiting"),
        ];
    }
    
    public function canDelete()
    {
        if($this->status == self::STATUS_WAITING)
            return true;
        
        return false;
    }
    
    public function delete()
    {
        if(!$this->canDelete())
            throw new InvalidStatus(__("Cannot delete rental"));
        
        ItemBill::where("rental_id", $this->id)->delete();
        Balance::where("rental_id", $this->id)->delete();
        
        return parent::delete();
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
            $c1 = self
                ::where("item_id", $itemId)
                ->whereIn("status", $statuses)
                ->where("start", "<=", $startDate)
                ->where(function($q) use($startDate) {
                    $q->where("end", ">=", $startDate)->orWhereNull("end");
                })
                ->count();
            
            $c2 = self
                ::where("item_id", $itemId)
                ->whereIn("status", $statuses)
                ->where("start", "<=", $endDate)
                ->where(function($q) use($endDate) {
                    $q->where("end", ">=", $endDate)->orWhereNull("end");
                })
                ->count();
            
            $c3 = self
                ::where("item_id", $itemId)
                ->whereIn("status", $statuses)
                ->where(function($q) use($startDate, $endDate) {
                    $q
                        ->whereBetween("start", [$startDate, $endDate])
                        ->orWhereBetween("end", [$startDate, $endDate]);
                })
                ->count();
            
            if($c1 || $c2 || $c3)
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
            $this->end = self::calculateEndDate(strtotime($this->start), $this->months);
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
        return $this->tenant()->first();
    }
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->where("uuid", $this->uuid)->withoutGlobalScopes();
    }
    
    public function getItem()
    {
        return $this->item()->first();
    }
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->where("uuid", $this->uuid)->withoutGlobalScopes();
    }
    
    public static function checkWaitingRentals()
    {
        $time = time();
        $rentals = self
            ::where("status", self::STATUS_WAITING)
            ->where("start", "<=", $time)
            ->where(function($q) use($time) {
                $q->where("end", ">=", $time)->orWhereNull("end");
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
            $item->setRentedFlag();
    }
    
    private function setArchive()
    {
        $this->status = self::STATUS_ARCHIVE;
        $this->saveQuietly();
        
        $item = $this->item()->withoutGlobalScopes()->first();
        if($item)
            $item->setRentedFlag();
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
    
    public static function recalculate(Rental $rental)
    {
        $tenant = Customer::find($rental->tenant_id);
        if($tenant)
        {
            $totalActive = self::where("status", self::STATUS_CURRENT)->where("tenant_id", $rental->tenant_id)->count();
            $totalWaiting = self::where("status", self::STATUS_WAITING)->where("tenant_id", $rental->tenant_id)->count();
            
            $tenant->total_active_rentals = $totalActive;
            $tenant->total_waiting_rentals = $totalWaiting;
            $tenant->saveQuietly();
        }
    }
    
    public function setCurrentRentalImmediately()
    {
        if(!self::where("item_id", $this->item_id)->where("status", self::STATUS_CURRENT)->count())
        {
            if($this->getRawOriginal("start") <= time())
                $this->setCurrent();
        }
    }
    
    private function calculateNextRental(int $time)
    {
        $date = (new DateTime())
            ->setTimestamp($time)
            ->modify("first day of this month")
            ->add(new DateInterval("P1M"))
            ->add(new DateInterval("P" . ($this->payment_day-1) . "D"));
        
        return Helper::setDateTime($date, "23:59:59", true);
    }
    
    public function generateInitialItemBills()
    {
        if($this->deposit > 0)
        {
            $deposit = new ItemBill;
            $deposit->item_id = $this->item_id;
            $deposit->rental_id = $this->id;
            $deposit->bill_type_id = Data::getSystemBillTypes()["deposit"][0];
            $deposit->payment_date = $this->getRawOriginal("first_payment_date");
            $deposit->cost = $this->deposit;
            $deposit->save();
        }
        
        $cost = $this->rent;
        if($this->first_month_different_amount)
            $cost = $this->first_month_different_amount;
        
        $rent = new ItemBill;
        $rent->item_id = $this->item_id;
        $rent->rental_id = $this->id;
        $rent->bill_type_id = Data::getSystemBillTypes()["rent"][0];
        $rent->payment_date = $this->getRawOriginal("first_payment_date");
        $rent->cost = $cost;
        $rent->save();
        
        if($this->payment == self::PAYMENT_CYCLICAL)
        {
            $this->next_rental = $this->calculateNextRental($this->getRawOriginal("start"));
            $this->save();
        }
    }
}