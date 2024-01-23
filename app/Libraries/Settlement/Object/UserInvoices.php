<?php

namespace App\Libraries\Settlement\Object;

use Illuminate\Http\Request;
use App\Libraries\Settlement\Abstract\SettlementObject;

class UserInvoices extends SettlementObject
{
    public function getId()
    {
        return $this->object->id;
    }

    public function getAmount()
    {
        return $this->object->type == "correction" ? $this->object->balance_correction : $this->object->balance;
    }

    public function settlement($amount)
    {
        $this->object->recalculateBalance();
    }

    public function getObjectType()
    {
        return "invoice";
    }

    public static function load(\App\Models\Settlements $settlement)
    {
        $row = \App\Models\UserInvoices::find($settlement->object_id);
        if($row)
            return new self($row);

        return false;
    }

    public function delete()
    {
        $this->object->recalculateBalance();
    }
}
