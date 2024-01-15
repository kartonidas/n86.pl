<?php

namespace App\Console\Commands;

use Throwable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

use App\Libraries\FirebaseHelper;
use App\Models\Firm;

class FirebaseRegister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:firebase-register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create firebase user account';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $firms = Firm::withoutGlobalScope("uuid")->where("firebase", 0)->get();
        
        foreach($firms as $firm)
        {
            echo $firm->uuid . ": ";
            try
            {
                $password = Str::upper(Str::random(16));
                FirebaseHelper::createUser($firm->uuid, $password, "");
                
                $firm->firebase = 1;
                $firm->firebase_password = Crypt::encryptString($password);
                $firm->saveQuietly();
                
                echo "OK";
            }
            catch(Throwable $e) {
                echo $e->getMessage();
            }
            
            echo "\n";
        }
    }
}
