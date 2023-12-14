<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Firm extends Model
{
    use SoftDeletes;
    
    protected $hidden = ["uuid"];
    
    public function getOwner()
    {
        return User::where("firm_id", $this->id)->where("owner", 1)->first();
    }
    
    public static function getOwnerByUuid($uuid)
    {
        $firm = Firm::where("uuid", $uuid)->first();
        if($firm)
        {
            $user = User::where("firm_id", $firm->id)->where("owner", 1)->first();
            if($user)
                return $user;
        }
    }
}