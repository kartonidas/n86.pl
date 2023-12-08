<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserInvitation;
use App\Models\Rental;

class CheckCurrentRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-current-rentals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check current rentals and move to archive (if date is correct)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Rental::checkCurrentRentals();
    }
}
