<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CashRegisterHistory;

class Settlement extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }

    public $timestamps = false;
    protected $table = "settlements";

    public function canDelete()
    {
        if($this->document == "cash_register" && $this->object == "invoice")
        {
            $cnt = Settlements::where("document", "invoice")->where("document_id", $this->object_id)->count();
            if($cnt)
                return false;
        }

        return true;
    }

    public function getObject()
    {
        switch($this->object)
        {
            case "invoice":
                return \App\Models\UserInvoices::find($this->object_id);
            break;
        }
        return false;
    }

    public function prepare()
    {
        return \App\Libraries\Settlement\Manager::prepareSettlement($this);
    }
}
