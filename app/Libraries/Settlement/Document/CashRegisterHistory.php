<?php

namespace App\Libraries\Settlement\Document;

use Illuminate\Http\Request;
use App\Libraries\Settlement\Abstract\SettlementDocument;

class CashRegisterHistory extends SettlementDocument
{
    public function getId()
    {
        return $this->document->id;
    }

    public function getBalance()
    {
        return $this->document->balance;
    }

    public function getDocumentType()
    {
        return "cash_register";
    }

    public function settlement($amount)
    {
        $this->document->recalculateBalance();
    }

    public static function load(\App\Models\Settlements $settlement)
    {
        $row = \App\Models\CashRegisterHistory::find($settlement->document_id);
        if($row)
            return new self($row);

        return false;
    }

    public function getFullNumber()
    {
        return $this->document->full_number;
    }

    public function getDetails()
    {
        return route("panel.cash_register.update", $this->getId());
    }

    public function getTitle()
    {
        return $this->document->title;
    }

    public function getCashRegister()
    {
        $cashRegistries = \App\Models\ConfigCashRegister::getRegistries(null, false, true);
        if(!empty($cashRegistries[$this->document->cash_register_id]))
            return $cashRegistries[$this->document->cash_register_id];
        return false;
    }

    public function getOrigAmount()
    {
        return $this->document->amount;
    }

    public function delete()
    {
        $this->document->recalculateBalance();
    }
}
