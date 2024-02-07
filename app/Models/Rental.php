<?php

namespace App\Models;

use DateTime;
use DateInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\InvalidRentalDates;
use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Libraries\Data;
use App\Libraries\Helper;
use App\Mail\UserNotification\RentalEndedSingle;
use App\Models\Balance;
use App\Models\BalanceDocument;
use App\Models\Config;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\User;
use App\Traits\NumberingTrait;

class Rental extends Model
{
    use SoftDeletes, NumberingTrait;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    const STATUS_ARCHIVE = "archive";
    const STATUS_CURRENT = "current";
    const STATUS_WAITING = "waiting";
    const STATUS_TERMINATION = "termination";
    const STATUS_CANCELED = "canceled";
    
    const PERIOD_MONTH = "month";
    const PERIOD_INDETERMINATE = "indeterminate";
    const PERIOD_DATE = "date";
    const PERIOD_TERM_MONTHS = "months";
    const PERIOD_TERM_DAYS = "days";
    const PAYMENT_CYCLICAL = "cyclical";
    const PAYMENT_ONETIME = "onetime";
    
    public static $sortable = ["start", "status", "end", "full_number", "rent", "balance"];
    public static $defaultSortable = ["start", "desc"];
    
    protected $casts = [
        "deposit" => "float",
        "rent" => "float",
        "first_month_different_amount" => "float",
        "last_month_different_amount" => "float",
        "balance" => "float",
    ];
    protected $hidden = ["uuid"];
    
    
    protected function startString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["start"] ? date("Y-m-d", $attributes["start"]) : null,
        );
    }
    
    protected function endString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["end"] ? date("Y-m-d", $attributes["end"]) : null,
        );
    }
    
    protected function firstPaymentDateString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["first_payment_date"] ? date("Y-m-d", $attributes["first_payment_date"]) : null,
        );
    }
    
    protected function terminationTimeString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["termination_time"] ? date("Y-m-d", $attributes["termination_time"]) : null,
        );
    }
    
    protected function terminationAddedString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["termination_added"] ? date("Y-m-d", $attributes["termination_added"]) : null,
        );
    }
    
    public function prepareViewData()
    {
        $this->start = $this->startString;
        $this->end = $this->endString;
        $this->first_payment_date = $this->firstPaymentDateString;
        $this->termination_time = $this->terminationTimeString;
        $this->termination_added = $this->terminationAddedString;
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
    
    public static function getStatuses($duringTermination = false)
    {
        $statuses = [
            self::STATUS_ARCHIVE => __("Archive"),
            self::STATUS_CURRENT => __("Current"),
            self::STATUS_WAITING => __("Waiting"),
            self::STATUS_TERMINATION => __("Termination"),
            self::STATUS_CANCELED => __("Canceled"),
        ];
        
        if($duringTermination)
            $statuses["during_termination"] = __("During termination");
        
        return $statuses;
    }
    
    public function getMaskNumber()
    {
        return self::getMaskNumberStatic();
    }

    public static function getMaskNumberStatic()
    {
        $out = [];
        $config = Config::getConfig("basic");
        $out["mask"] = !empty($config["rental_numbering_mask"]) ? $config["rental_numbering_mask"] : config("params.default_mask.rental.mask");
        $out["continuation"] = !empty($config["rental_numbering_continuation"]) ? $config["rental_numbering_continuation"] : config("params.default_mask.rental.continuation");
        return $out;
    }

    public function canUpdate()
    {
        if(in_array($this->status, [self::STATUS_WAITING, self::STATUS_CURRENT]) && !$this->termination)
        {
            $item = $this->item()->first();
            if(!$item || $item->mode != Item::MODE_NORMAL)
                return false;
            
            return true;
        }
        
        return false;
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
    
    public static function checkDates(array $rent, int $itemId = null, $rentalId = null)
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
                    $q->where(function($q2) use($startDate) {
                        $q2
                            ->where("termination", 1)
                            ->where("termination_time", ">=", $startDate);
                    })
                    ->orWhere(function($q2) use($startDate) {
                        $q2
                            ->where("termination", 0)
                            ->where(function($q3) use($startDate) {
                                $q3
                                    ->where("end", ">=", $startDate)
                                    ->orWhereNull("end");
                            });
                    });
                });
            if($rentalId)
                $c1->where("id", "!=", $rentalId);
            $c1 = $c1->count();
            
            $c2 = self
                ::where("item_id", $itemId)
                ->whereIn("status", $statuses)
                ->where("start", "<=", $endDate)
                ->where(function($q) use($startDate) {
                    $q->where(function($q2) use($startDate) {
                        $q2
                            ->where("termination", 1)
                            ->where("termination_time", ">=", $startDate);
                    })
                    ->orWhere(function($q2) use($startDate) {
                        $q2
                            ->where("termination", 0)
                            ->where(function($q3) use($startDate) {
                                $q3
                                    ->where("end", ">=", $startDate)
                                    ->orWhereNull("end");
                            });
                    });
                });
            if($rentalId)
                $c2->where("id", "!=", $rentalId);
            $c2 = $c2->count();
            
            $c3 = self
                ::where("item_id", $itemId)
                ->whereIn("status", $statuses)
                ->where(function($q) use($startDate, $endDate) {
                    $q
                        ->whereBetween("start", [$startDate, $endDate])
                        ->orWhere(function($q2) use($startDate, $endDate) {
                            $q2
                                ->where(function($q3) use($startDate, $endDate) {
                                    $q3->where("termination", 0)->whereBetween("end", [$startDate, $endDate]);
                                })
                                ->orWhere(function($q3) use($startDate, $endDate) {
                                    $q3->where("termination", 1)->whereBetween("termination_time", [$startDate, $endDate]);
                                });
                        });
                });
            if($rentalId)
                $c3->where("id", "!=", $rentalId);
            $c3 = $c3->count();
            
            if($c1 || $c2 || $c3)
                throw new InvalidRentalDates(__("Cannot rented during the given time period"));
        }
        elseif($startDate)
        {
            // na czas nieokreślony nie moga istnieć żadne przyszłe rezerwacje!!!!!!
            $c1 = self::where("item_id", $itemId)
                ->whereIn("status", $statuses)
                ->where("start", "<=", $startDate)
                ->where(function($q) use($startDate) {
                    $q->where(function($q2) use($startDate) {
                        $q2
                            ->where("termination", 1)
                            ->where("termination_time", ">=", $startDate);
                    })
                    ->orWhere(function($q2) use($startDate) {
                        $q2
                            ->where("termination", 0)
                            ->where(function($q3) use($startDate) {
                                $q3
                                    ->where("end", ">=", $startDate)
                                    ->orWhereNull("end");
                            });
                    });
                });
            if($rentalId)
                $c1->where("id", "!=", $rentalId);
            $c1 = $c1->count();
            
            $c2 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", ">=", $startDate);
            if($rentalId)
                $c2->where("id", "!=", $rentalId);
            $c2 = $c2->count();
            
            //$c3 = self::where("item_id", $itemId)->whereIn("status", $statuses)->where("start", "<=", $startDate)->whereNull("end");
            //if($rentalId)
            //    $c3->where("id", "!=", $rentalId);
            //$c3 = $c3->count();
            
            if($c1 || $c2)
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
    
    public static function checkTerminationRentals()
    {
        $time = time();
        $rentals = self
            ::where("status", self::STATUS_CURRENT)
            ->where("termination", 1)
            ->where("termination_time", "<", $time)
            ->withoutGlobalScopes()
            ->get();
            
        foreach($rentals as $rental)
            $rental->setTerminated();
    }
    
    private function setWaiting()
    {
        DB::transaction(function () {
            $this->status = self::STATUS_WAITING;
            $this->saveQuietly();
            
            $item = $this->item()->withoutGlobalScopes()->first();
            if($item)
                $item->setRentedFlag();
        });
    }
    
    private function setCurrent()
    {
        DB::transaction(function () {
            $this->status = self::STATUS_CURRENT;
            $this->saveQuietly();
            
            $item = $this->item()->withoutGlobalScopes()->first();
            if(!$item || !$item->canAddRental())
                throw new InvalidStatus(__("Cannot rented item"));
            
            $item->setRentedFlag();
        });
    }
    
    private function setArchive()
    {
        $this->status = self::STATUS_ARCHIVE;
        $this->saveQuietly();
        
        $item = $this->item()->withoutGlobalScopes()->first();
        if($item)
            $item->setRentedFlag();
            
        $item->notifyRentalEnded();
    }
    
    private function setTerminated()
    {
        $this->status = self::STATUS_TERMINATION;
        $this->saveQuietly();
        
        $item = $this->item()->withoutGlobalScopes()->first();
        if($item)
            $item->setRentedFlag();
        
        $item->notifyRentalEnded();
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
                else
                {
                    if($time > $this->end)
                        return self::STATUS_ARCHIVE;
                }
            }
        }
        elseif($this->start)
        {
            if($this->start > $time)
                return self::STATUS_WAITING;
            else
                return self::STATUS_CURRENT;
        }
        
        return self::STATUS_WAITING;
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
            if($this->start <= time() && ($this->period == self::PERIOD_INDETERMINATE || $this->end > time()))
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
            $deposit->payment_date = $this->first_payment_date;
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
        $rent->payment_date = $this->first_payment_date;
        $rent->cost = $cost;
        $rent->save();
        
        if($this->payment == self::PAYMENT_CYCLICAL)
        {
            $this->next_rental = $this->calculateNextRental($this->start);
            $this->save();
        }
    }
    
    public function terminate(int $end, string $reason)
    {
        if($this->status != Rental::STATUS_CURRENT)
            throw new InvalidStatus(__("Rental in incorrect status"));
        
        if($this->termination)
            throw new InvalidStatus(__("The rental is already being terminated"));
        
        $this->termination = 1;
        $this->termination_time = $end;
        $this->termination_added = time();
        $this->termination_reason = $reason;
        $this->save();
    }
    
    public function terminateImmediately(string $reason)
    {
        if($this->status != Rental::STATUS_CURRENT)
            throw new InvalidStatus(__("Rental in incorrect status"));
        
        if($this->termination)
            throw new InvalidStatus(__("The rental is already being terminated"));
        
        $this->termination = 1;
        $this->termination_time = time();
        $this->termination_added = time();
        $this->termination_reason = $reason;
        $this->save();
        
        $this->setTerminated();
    }
    
    public function hasPaidDeposit()
    {
        $depositBill = ItemBill::where("rental_id", $this->id)->where("bill_type_id", Data::getSystemBillTypes()["deposit"][0])->first();
        if($depositBill)
        {
            $balanceDocument = BalanceDocument::where("object_type", BalanceDocument::OBJECT_TYPE_BILL)->where("object_id", $depositBill->id)->first();
            if($balanceDocument && $balanceDocument->paid)
                return true;
        }
        return false;
    }
    
    public function hasGeneratedRent()
    {
        if(ItemBill::where("rental_id", $this->id)->where("bill_type_id", Data::getSystemBillTypes()["rent"][0])->count() > 0)
            return true;
        return false;
    }
    
    public function updateRentalStatusFlag()
    {
        if($this->status == self::STATUS_CURRENT)
        {
            if($this->start > time())
                $this->setWaiting();
        }
        
        if($this->status == self::STATUS_WAITING)
            $this->setCurrentRentalImmediately();
    }
    
    public function updateItemBills()
    {
        $rents = ItemBill
            ::where("rental_id", $this->id)
            ->where("payment_date", ">", time())
            ->where("bill_type_id", Data::getSystemBillTypes()["rent"][0])
            ->where("paid", 0)
            ->get();
            
        foreach($rents as $rent)
        {
            $rent->cost = $this->rent;
            $rent->save();
        }
        
        $depositBill = ItemBill
            ::where("rental_id", $this->id)
            ->where("bill_type_id", Data::getSystemBillTypes()["deposit"][0])
            ->first();
            
        if($depositBill)
        {
            $depositBill->cost = $this->deposit;
            $depositBill->save();
        }
        elseif($this->deposit > 0)
        {
            $deposit = new ItemBill;
            $deposit->item_id = $this->item_id;
            $deposit->rental_id = $this->id;
            $deposit->bill_type_id = Data::getSystemBillTypes()["deposit"][0];
            $deposit->payment_date = $this->first_payment_date;
            $deposit->cost = $this->deposit;
            $deposit->save();
        }
    }
    
    public function getBalanceRow()
    {
        return Balance::where("item_id", $this->item_id)->where("rental_id", $this->id)->first();
    }
    
    public function getUnpaidBalanceDocuments()
    {
        $balance = $this->getBalanceRow();
        if($balance)
        {
            return BalanceDocument
                ::where("item_id", $this->item_id)
                ->where("balance_id", $balance->id)
                ->where("paid", 0)
                ->where("amount", "<", 0)
                ->get();
        }
    }
    
    public function scopeActive(Builder $query): void
    {
        $query->whereIn("status", [self::STATUS_CURRENT, self::STATUS_WAITING]);
    }
    
    private function notifyRentalEnded()
    {
        $configuredNotifications = ConfigNotification
            ::withoutGlobalScopes()
            ->where("uuid", $this->uuid)
            ->where("type", ConfigNotification::TYPE_RENTAL_ENDED)
            ->get();
        
        $item = $this->item()->first();
        if(!$item || $item->mode == Item::MODE_ARCHIVED)
            return;
        
        $data = [
            "rental" => $this->toArray(),
            "item" => $item->toArray(),
        ];
        
        foreach($configuredNotifications as $notification)
        {
            $user = User::find($notification->owner_id);
            if(!$user || $user->deleted)
                continue;
            
            Mail::to($user->email)->locale($user->default_locale)->queue(new RentalEndedSingle($data, $notification));
        }
    }
}