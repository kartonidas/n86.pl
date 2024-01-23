<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
            "payment_types" => __("Payment types")
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
}