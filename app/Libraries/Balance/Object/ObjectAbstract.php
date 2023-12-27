<?php

namespace App\Libraries\Balance\Object;

use Illuminate\Database\Eloquent\Model;
use App\Models\BalanceDocument;

abstract class ObjectAbstract {
    protected $id;
    protected $itemId;
    protected $rentalId;
    protected $objectType;
    protected $objectId;
    protected $amount;
    protected $documentIds;
    protected $paidDate;
    protected $paymentMethod;
    protected $comments;
    
    abstract public static function makeFromModel(Model $object);
    abstract public static function makeFromParams(int $itemId, int $rentalId, string $objectType, int $objectId, float $amount);
    abstract public static function makeFromBalanceDocument(BalanceDocument $document);
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getItemId()
    {
        return $this->itemId;
    }
    
    public function getRentalId()
    {
        return $this->rentalId;
    }
    
    public function getObjectType()
    {
        return $this->objectType;
    }
    
    public function getObjectId()
    {
        return $this->objectId;
    }
    
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }
    
    public function getAmount()
    {
        return $this->amount;
    }
    
    public function setDocumentIds($documentIds)
    {
        $this->documentIds = $documentIds;
    }
    
    public function getDocumentIds()
    {
        return $this->documentIds;
    }
    
    public function setPayment(int $paidDate, string $paymentMethod)
    {
        $this->paidDate = $paidDate;
        $this->paymentMethod = $paymentMethod;
    }
    
    public function getPayment()
    {
        return [
            "paid_date" => $this->paidDate,
            "payment_method" => $this->paymentMethod,
        ];
    }
    
    public function setComment(string $comments)
    {
        $this->comments = $comments;
    }
    
    public function getComment()
    {
        return $this->comments;
    }
}
