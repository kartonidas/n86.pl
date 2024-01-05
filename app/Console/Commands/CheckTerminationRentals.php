<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserInvitation;
use App\Models\Rental;

class CheckTerminationRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-termination-rentals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check termination rentals and move to termination status (if date is correct)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Rental::checkTerminationRentals();
    }
}
