<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rental;

class AppDevPatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:app-dev-patch';

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
        // Generate empty rental numbers
        $rentals = Rental::withoutGlobalScopes()->whereNull("number")->get();
        foreach($rentals as $rental)
        {
            $rental->setNumber();
        }
    }
}
