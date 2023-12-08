<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserInvitation;
use App\Models\Rental;

class CheckWaitingRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-waiting-rentals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check waiting rentals and move to current (if date is correct)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Rental::checkWaitingRentals();
    }
}
