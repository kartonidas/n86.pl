<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use App\Exceptions\InvalidStatus;
use App\Models\CustomerInvoice;
use App\Models\Fault;
use App\Models\ItemBill;
use App\Models\ItemCyclicalFee;
use App\Models\User;

class Dictionary extends Model
{
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    protected $hidden = ["uuid"];
    
    public static function getAllowedTypes()
    {
        return [
            "fees" => __("Fee included in the rent"),
            "bills" => __("Type of bill"),
            "payment_types" => __("Payment types"),
            "fault_statuses" => __("Payment types")
        ];
    }
    
    public static function createDefaultDictionaries(User $user)
    {
        $dictionaries = config("default." . $user->default_locale . ".dictionaries");
        
        if(empty($dictionaries) && $user->default_locale != "pl")
            $dictionaries = config("default.pl.dictionaries");
        
        if(!empty($dictionaries))
        {
            foreach($dictionaries as $type => $dict)
            {
                foreach($dict as $d)
                {
                    $row = new self;
                    $row->uuid = $user->getUuid();
                    $row->type = $type;
                    $row->active = 1;
                    $row->name = $d;
                    $row->saveQuietly();
                }
            }
        }
    }
    
    public function delete()
    {
        if(!$this->canDelete())
            throw new InvalidStatus(__("Cannot delete dictionary"));
        
        return parent::delete();
    }
    
    public function canDelete()
    {
        switch($this->type)
        {
            case "bills":
                if(ItemBill::where("bill_type_id", $this->id)->count() || ItemCyclicalFee::where("bill_type_id", $this->id)->count())
                    return false;
            break;
        
            case "payment_types":
                if(CustomerInvoice::where("payment_type_id", $this->id)->count())
                    return false;
            break;
        
            case "fault_statuses":
                if(Fault::where("status_id", $this->id)->count())
                    return false;
            break;
        }
        
        return true;
    }
}