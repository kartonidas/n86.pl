<?php

namespace App\Libraries\Settlement\Document;

use Illuminate\Http\Request;
use App\Libraries\Settlement\Abstract\SettlementDocument;

class UserInvoices extends SettlementDocument
{
    public function getId()
    {
        return $this->document->id;
    }

    public function getBalance()
    {
        $amount = \App\Models\Settlements::where("document", $this->getDocumentType())->where("document_id", $this->document->id)->sum("amount");
        if($this->document->total_payments - $amount > 0)
            return $this->document->total_payments - $amount;

        return 0;
    }

    public function getDocumentType()
    {
        return "invoice";
    }

    public function settlement($amount)
    {
    }

    public static function load(\App\Models\Settlements $settlement)
    {
        $row = \App\Models\UserInvoices::find($settlement->document_id);
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
        if($this->document->type == "correction")
            return route("panel.user_invoices.correction_update", $this->getId());
        else
            return route("panel.user_invoices.update", $this->getId());
    }

    public function getTitle()
    {
        return "Rozliczenie za pomocą dokumentu: " . $this->getFullNumber();
    }

    public function getOrigAmount()
    {
        return $this->document->total_payments;
    }
}
