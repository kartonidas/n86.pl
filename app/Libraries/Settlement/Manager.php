<?php

namespace App\Libraries\Settlement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Manager
{
    public static function settlement($document, $object)
    {
        DB::transaction(function () use($document, $object) {
            $documentManager = self::getDocument($document);
            $objectManager = self::getObject($object);

            $objectAmount = $objectManager->getAmount();
            $documentBalance = $documentManager->getBalance();

            if($documentBalance >= $objectAmount)
                $amount = $objectAmount;
            else
                $amount = $documentBalance;

            $cashToObject = new \App\Models\Settlements;
            $cashToObject->document = $documentManager->getDocumentType();
            $cashToObject->document_id = $documentManager->getId();
            $cashToObject->object = $objectManager->getObjectType();
            $cashToObject->object_id = $objectManager->getId();
            $cashToObject->amount = $amount;
            $cashToObject->payment_date = date("Y-m-d");
            $cashToObject->save();

            $documentManager->settlement($amount);
            $objectManager->settlement($amount);
        });
    }

    private static function getDocument($document)
    {
        $documentManager = null;
        switch(get_class($document))
        {
            case \App\Models\CashRegisterHistory::class:
                $documentManager = new \App\Libraries\Settlement\Document\CashRegisterHistory($document);
            break;

            case \App\Models\UserInvoices::class:
                $documentManager = new \App\Libraries\Settlement\Document\UserInvoices($document);
            break;
        }

        if(!$documentManager)
            throw new \Exception("Nieobsługiwany rodzaj dokumentu");

        return $documentManager;
    }

    private static function getObject($object)
    {
        $objectManager = null;
        switch(get_class($object))
        {
            case \App\Models\UserInvoices::class:
                $objectManager = new \App\Libraries\Settlement\Object\UserInvoices($object);
            break;
        }

        if(!$objectManager)
            throw new \Exception("Nieobsługiwany rodzaj obiektu");

        return $objectManager;
    }

    private static function loadDocument(\App\Models\Settlements $row)
    {
        $document = false;
        switch($row->document)
        {
            case "cash_register":
                $document = \App\Libraries\Settlement\Document\CashRegisterHistory::load($row);
            break;

            case "invoice":
                $document = \App\Libraries\Settlement\Document\UserInvoices::load($row);
            break;
        }

        return $document;
    }

    private static function loadObject(\App\Models\Settlements $row)
    {
        $object = false;
        switch($row->object)
        {
            case "invoice":
                $object = \App\Libraries\Settlement\Object\UserInvoices::load($row);
            break;
        }

        return $object;
    }

    public static function prepareSettlement(\App\Models\Settlements $row)
    {
        $document = self::loadDocument($row);

        $row = $row->toArray();
        if($document)
        {
            $row["full_number"] = $document->getFullNumber();
            $row["details"] = $document->getDetails();
            $row["title"] = $document->getTitle();
            $row["cash_register_name"] = $document->getCashRegister();
            $row["orig_amount"] = $document->getOrigAmount();
        }

        return $row;
    }

    public static function deleteSettlement(\App\Models\Settlements $row)
    {
        $document = self::loadDocument($row);
        if($document)
            $document->delete();

        $object = self::loadObject($row);
        if($object)
            $object->delete();
    }
}
