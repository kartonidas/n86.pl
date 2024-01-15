<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;

use App\Jobs\RecalculateStats;
use App\Models\History;
use App\Models\Customer;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        History::log("create", $customer);
        RecalculateStats::dispatch($customer->uuid);
    }
    
    public function updated(Customer $customer): void
    {
        History::log("update", $customer);
        RecalculateStats::dispatch($customer->uuid);
    }
    
    public function deleted(Customer $customer): void
    {
        History::log("delete", $customer);
        RecalculateStats::dispatch($customer->uuid);
    }
}