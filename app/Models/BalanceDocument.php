<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Exceptions\InvalidStatus;
use App\Exceptions\ObjectNotExist;
use App\Models\Balance;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\Rental;
use App\Models\User;

class BalanceDocument extends Model
{
    use SoftDeletes;
    
    protected $casts = [
        "amount" => "float",
        "created_at" => 'datetime:Y-m-d H:i',
        "updated_at" => 'datetime:Y-m-d H:i',
    ];
    
    protected function paidDateString(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["paid_date"] ? date("Y-m-d", $attributes["paid_date"]) : null,
        );
    }
    
    public function prepareViewData()
    {
        $this->paid_date = $this->paidDateString;
    }
    
    const OBJECT_TYPE_BILL = "bill";
    const OBJECT_TYPE_DEPOSIT = "deposit";
    const PAYMENT_CASH = "cash";
    const PAYMENT_TRANSFER = "transfer";
    
    public static function getAvailablePaymentMethods()
    {
        return [
            self::PAYMENT_CASH => __("Cash"),
            self::PAYMENT_TRANSFER => __("Transfer"),
        ];
    }
    
    public static function getUnpaid($itemId)
    {
        return self
            ::where("item_id", $itemId)
            ->where("paid", 0)
            ->where("amount", "<", 0)
            ->get();
    }
    
    public function setBalance()
    {
        \DB::transaction(function () {
            $balance = Balance::where("id", $this->balance_id)->lockForUpdate()->first();
            
            if(!$balance)
                throw new ObjectNotExist(__("Balance object does not exist"));
            
            $balance->amount = self::where("item_id", $this->item_id)->where("balance_id", $balance->id)->sum("amount");
            $balance->save();
            
            $rental = Rental::withoutGlobalScopes()->find($balance->rental_id);
            if($rental)
            {
                $rental->balance = $balance->amount;
                $rental->save();
            }
            
            $itemBalance = Balance::ensureBalance($this->item_id);
            $itemBalance = Balance::where("id", $itemBalance->id)->lockForUpdate()->first();
            
            if(!$itemBalance)
                throw new ObjectNotExist(__("Balance object does not exist"));
            
            $itemBalance->amount = self::where("item_id", $this->item_id)->sum("amount");
            $itemBalance->save();
            
            $item = Item::withoutGlobalScopes()->find($this->item_id);
            if($item)
            {
                $item->balance = $itemBalance->amount;
                $item->save();
            }
        });
    }
    
    public function setObjectPaid()
    {
        switch($this->object_type)
        {
            case self::OBJECT_TYPE_BILL:
                $bill = ItemBill::find($this->object_id);
                if(!$bill)
                    throw new ObjectNotExist(__("Bill does not exists"));
                
                $bill->paid = $this->paid;
                $bill->paid_date = $this->paid_date;
                $bill->saveQuietly();
            break;
        }
    }
    
    public function canDelete()
    {
        return true;
    }
    
    public function delete()
    {
        if(!$this->canDelete())
            throw new InvalidStatus(__("Cannot delete balance document"));
        
        return parent::delete();
    }
    
    public function getDepositAssociatedDocument()
    {
        if($this->document_ids)
        {
            $documentIds = explode(",", $this->document_ids);
            return BalanceDocument::whereIn("id", $documentIds)->get();
        }
        
        return null;
    }
    
    private static $cacheCreatedBy = [];
    public function getCreatedBy()
    {
        if($this->user_id > 0)
        {
            if(!isset(static::$cacheCreatedBy[$this->user_id]))
                static::$cacheCreatedBy[$this->user_id] = User::withTrashed()->find($this->user_id);
                
            return static::$cacheCreatedBy[$this->user_id];
        }
    }
}
