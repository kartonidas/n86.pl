<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Exceptions\InvalidStatus;
use App\Libraries\Balance\Balance;
use App\Libraries\Balance\Object\DepositObject;
use App\Libraries\Data;
use App\Libraries\Helper;
use App\Models\BalanceDocument;
use App\Models\Dictionary;
use App\Models\Item;
use App\Models\Rental;

class ItemBill extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $casts = [
        "cost" => "float",
    ];
    protected $hidden = ["uuid"];
    
    protected function paymentDate(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
    protected function paidDate(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
    protected function sourceDocumentDate(): Attribute
    {
        return Attribute::make(
            get: fn (int|null $value) => $value ? date("Y-m-d", $value) : null,
        );
    }
    
    public function canDelete()
    {
        return true;
    }
    
    public function delete()
    {
        $balanceDocument = $this->getBalanceDocument();
        if($balanceDocument)
            $balanceDocument->delete();
        
        if(!$this->canDelete())
            throw new InvalidStatus(__("Cannot delete object"));
        
        return parent::delete();
    }
    
    private static $cachedBillTypes = [];
    public function getBillType()
    {
        if($this->bill_type_id < 0)
        {
            $systemBillTypes = Data::getSystemBillTypes();
            foreach($systemBillTypes as $systemBillType)
            {
                if($systemBillType[0] == $this->bill_type_id)
                    return ["name" => $systemBillType[1]];
            }
        }
        
        if(!isset(self::$cachedBillTypes[$this->bill_type_id]))
            self::$cachedBillTypes[$this->bill_type_id] = Dictionary::find($this->bill_type_id);
        
        return self::$cachedBillTypes[$this->bill_type_id];
    }
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    
    public function getBalanceDocument()
    {
        return BalanceDocument::where("object_type", BalanceDocument::OBJECT_TYPE_BILL)->where("object_id", $this->id)->first();
    }
    
    public function paid(int|null $paidDate = null, string $paymentMethod = BalanceDocument::PAYMENT_CASH)
    {
        if($this->paid)
            throw new InvalidStatus(__("Bill is already paid"));
        
        if($paidDate === null)
            $paidDate = time();
        
        $balanceDocument = $this->getBalanceDocument();
        if($balanceDocument)
        {
            $balanceDocument->paid = 1;
            $balanceDocument->paid_date = $paidDate;
            $balanceDocument->payment_method = $paymentMethod;
            $balanceDocument->saveQuietly();
        }
        
        $this->paid = 1;
        $this->paid_date = $paidDate;
        $this->saveQuietly();
        
        return true;
    }
    
    public function unpaid()
    {
        if(!$this->paid)
            throw new InvalidStatus(__("Bill is already unpaid"));
        
        $balanceDocument = $this->getBalanceDocument();
        if($balanceDocument)
        {
            $balanceDocument->paid = 0;
            $balanceDocument->paid_date = null;
            $balanceDocument->payment_method = null;
            $balanceDocument->saveQuietly();
        }
        
        $this->paid = 0;
        $this->paid_date = null;
        $this->saveQuietly();
        
        return true;
    }
    
    public function deposit(float $cost, string $paidData, string $paymentMethod)
    {
        $document = $this->getBalanceDocument();
        if(!$document)
            throw new ObjectNotExist(__("Balance document does not exists"));
        
        if($cost != abs($document->amount))
            throw new InvalidStatus(__("Value of the documents exceeds the amount paid"));
        
        $deposit = DepositObject::makeFromParams($this->item_id, $this->rental_id, BalanceDocument::OBJECT_TYPE_DEPOSIT, 0, $cost);
        $deposit->setDocumentIds([$document->id]);
        $deposit->setPayment(Helper::setDateTime($paidData, "00:00:00", true), $paymentMethod);
        Balance::deposit($deposit)->create();
        
        return true;
    }
    
    public function isOutOfDate()
    {
        if(!$this->paid && $this->getAttributes()["payment_date"] <= time())
            return true;
        
        return false;
    }
    
    private static $cacheRentals = [];
    public function getRental()
    {
        if($this->rental_id > 0)
        {
            if(empty(static::$cacheRentals[$this->rental_id]))
            {
                $rental = Rental::withTrashed()->find($this->rental_id);
                if($rental)
                    static::$cacheRentals[$this->rental_id] = $rental;
            }
            
            return static::$cacheRentals[$this->rental_id] ?? null;
        }
    }
}