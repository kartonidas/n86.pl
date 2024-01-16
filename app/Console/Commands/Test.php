<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Firm;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Notification;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $subscription = Subscription::withoutGlobalScopes()->find(7);
        if($subscription)
        {
            $owner = self::getOwnerByUuid($subscription->uuid);
            Notification::notify($owner->id, -1, $subscription->id, "subscription:expired");
        }
    }
    
    private static function getOwnerByUuid($uuid)
    {
        $firm = Firm::where("uuid", $uuid)->first();
        if($firm)
        {
            $user = User::withoutGlobalScope("uuid")->where("firm_id", $firm->id)->where("owner", 1)->first();
            if($user)
                return $user;
        }
    }
}
