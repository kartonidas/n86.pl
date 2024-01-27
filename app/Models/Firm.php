<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Jobs\RecalculateStats;
use App\Libraries\FirebaseHelper;
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
    
    public static function stats(string $uuid)
    {
        $stats = [
            "items" => \App\Models\Item::withoutGlobalScope("uuid")->where("uuid", $uuid)->count(),
            "customers" => \App\Models\Customer::withoutGlobalScope("uuid")->where("uuid", $uuid)->where("role", Customer::ROLE_CUSTOMER)->count(),
            "tenants" => \App\Models\Customer::withoutGlobalScope("uuid")->where("uuid", $uuid)->where("role", Customer::ROLE_TENANT)->count(),
            "rentals" => \App\Models\Rental::withoutGlobalScope("uuid")->active()->where("uuid", $uuid)->count(),
        ];
        
        @mkdir(storage_path("accounts/" . $uuid), 0777, true);
        chmod(storage_path("accounts"), 0777);
        chmod(storage_path("accounts/" . $uuid), 0777);
        
        $file = storage_path("accounts/" . $uuid . "/stats.json");
        $fp = fopen($file, "w");
        fwrite($fp, json_encode($stats));
        fclose($fp);
        
        FirebaseHelper::updateStats($uuid, $stats);
    }
}