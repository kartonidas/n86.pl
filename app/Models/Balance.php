<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\Exceptions\ObjectNotExist;

class Balance extends Model
{
    use SoftDeletes;
    use \App\Traits\UuidTrait {
        boot as traitBoot;
    }
    
    public static function ensureBalance($itemId, $rentalId = 0)
    {
        return DB::transaction(function () use($itemId, $rentalId) {
            $balance = Balance::where("item_id", $itemId)->where("rental_id", $rentalId)->lockForUpdate()->first();
            if(!$balance)
            {
                $balance = new self;
                $balance->item_id = $itemId;
                $balance->rental_id = $rentalId;
                $balance->amount = 0;
                $balance->save();
            }
            return $balance;
        });
    }
    
    public static function getCurrentBalance($itemId, $rentalId = 0) : float
    {
        return DB::transaction(function () use($itemId, $rentalId) {
            $balance = Balance::where("item_id", $itemId)->where("rental_id", $rentalId)->lockForUpdate()->first();
            if(!$balance)
                throw new ObjectNotExist(__("Balance object does not exist"));
            
            return $balance->amount;
        });
    }
}
