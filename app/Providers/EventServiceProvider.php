<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\UserInvitation;
use App\Observers\UserInvitationObserver;
use App\Models\File;
use App\Observers\FileObserver;
use App\Models\Firm;
use App\Observers\FirmObserver;
use App\Models\Rental;
use App\Observers\RentalObserver;
use App\Models\Item;
use App\Observers\ItemObserver;
use App\Models\ItemBill;
use App\Observers\ItemBillObserver;
use App\Models\ItemCyclicalFee;
use App\Observers\ItemCyclicalFeeObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        UserInvitation::observe(UserInvitationObserver::class);
        File::observe(FileObserver::class);
        Firm::observe(FirmObserver::class);
        Rental::observe(RentalObserver::class);
        Item::observe(ItemObserver::class);
        ItemBill::observe(ItemBillObserver::class);
        ItemCyclicalFee::observe(ItemCyclicalFeeObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
