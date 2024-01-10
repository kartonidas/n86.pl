<?php

namespace App\Libraries\Balance;

use Illuminate\Support\Facades\DB;
use App\Models\BalanceDocument;

use App\Exceptions\InvalidStatus;

use App\Libraries\Balance\Abstract\BalanceAbstract;
use App\Libraries\Balance\Object\ChargeObject;
use App\Libraries\Balance\Object\DepositObject;

class Deposit extends BalanceAbstract
{
    public function __construct(DepositObject $operationObject)
    {
        $this->operationObject = $operationObject;
    }
    
    public static function make(ChargeObject|DepositObject $object)
    {
        return new self($object);
    }
    
    public function create()
    {
        DB::transaction(function () {
            $document = self::createBalanceDocument($this->operationObject);
            if(!$document)
                throw new ObjectNotExist(__("Cannot create balance document"));
            
            $document->amount = $this->operationObject->getAmount();
            $document->paid_date = $this->operationObject->getPayment()["paid_date"];
            $document->payment_method = $this->operationObject->getPayment()["payment_method"];
            $document->comments = $this->operationObject->getComment();
            
            if($this->operationObject->getDocumentIds())
                $document->document_ids = implode(",", $this->operationObject->getDocumentIds());
            $document->save();
            
            $associatedDocuments = $document->getDepositAssociatedDocument();
            
            if($associatedDocuments)
            {
                foreach($associatedDocuments as $associatedDocument)
                {
                    if($associatedDocument->paid)
                        throw new InvalidStatus(__("Document is already paid"));
                    
                    $associatedDocument->paid = 1;
                    $associatedDocument->paid_date = $document->getAttributes()["paid_date"];
                    $associatedDocument->source_paid_document = $document->id;
                    $associatedDocument->save();
                }
            }
        });
    }
    
    public function update()
    {
        DB::transaction(function () {
            $document = self::getBalanceDocument($this->operationObject);
            if(!$document)
                throw new ObjectNotExist(__("Balance document does not exists"));
            
            $document->amount = $this->operationObject->getAmount();
            $document->paid_date = $this->operationObject->getPayment()["paid_date"];
            $document->payment_method = $this->operationObject->getPayment()["payment_method"];
            $document->comments = $this->operationObject->getComment();
            $document->save();
            
            $associatedDocuments = $document->getDepositAssociatedDocument();
            if($associatedDocuments)
            {
                foreach($associatedDocuments as $associatedDocument)
                {
                    $associatedDocument->paid_date = $document->getAttributes()["paid_date"];
                    $associatedDocument->save();
                }
            }
        });
    }
    
    public function delete()
    {
        DB::transaction(function () {
            $document = self::getBalanceDocument($this->operationObject);
            if(!$document)
                throw new ObjectNotExist(__("Balance document does not exists"));
            
            $associatedDocuments = $document->getDepositAssociatedDocument();
            if($associatedDocuments)
            {
                foreach($associatedDocuments as $associatedDocument)
                {
                    $associatedDocument->paid = 0;
                    $associatedDocument->paid_date = null;
                    $associatedDocument->source_paid_document = null;
                    $associatedDocument->save();
                }
            }
            
            $document->delete();
        });
    }
}