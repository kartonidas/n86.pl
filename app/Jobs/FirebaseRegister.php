<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Libraries\FirebaseHelper;
use App\Models\Firm;

class FirebaseRegister implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $uuid)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try
        {
            $password = Str::upper(Str::random(16));
            FirebaseHelper::createUser($this->uuid, $password, "");
            
            $firm = Firm::where("uuid", $this->uuid)->first();
            if($firm)
            {
                $firm->firebase = 1;
                $firm->firebase_password = Crypt::encryptString($password);
                $firm->saveQuietly();
            }
        }
        catch(Exception $e) {}
    }
    
    public function uniqueId(): string
    {
        return $this->uuid;
    }
}
