<?php

namespace App\Libraries\Settlement\Abstract;

use Illuminate\Http\Request;

abstract class SettlementDocument
{
    protected $document;
    public function __construct($document)
    {
        $this->document = $document;
    }

    public function getCashRegister()
    {
        return "";
    }

    public function delete()
    {
    }

    abstract public function getBalance();
    abstract public function getDocumentType();
    abstract public function settlement($amount);
    abstract public function getId();
    abstract public static function load(\App\Models\Settlements $settlement);
    abstract public function getFullNumber();
    abstract public function getDetails();
    abstract public function getTitle();
    abstract public function getOrigAmount();
}
